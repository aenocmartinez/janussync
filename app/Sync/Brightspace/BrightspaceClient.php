<?php

namespace App\Sync\Brightspace;

final class BrightspaceClient
{
    private \D2LUserContext $ctx;
    private string $v      = '1.43'; // cursos/usuarios/inscripciones
    private string $vRoles = '1.46'; // roles

    public function __construct()
    {
        $cfg = config('brightspace');
        $lib = $cfg['libpath'];

        require_once $lib . '/D2LAppContextFactory.php';
        require_once $lib . '/D2LHostSpec.php';

        $factory = new \D2LAppContextFactory();
        $auth    = $factory->createSecurityContext($cfg['app_id'], $cfg['app_key']);
        $this->ctx = $auth->createUserContextFromHostSpec(
            new \D2LHostSpec($cfg['host'], $cfg['port'], $cfg['scheme']),
            $cfg['user_id'],
            $cfg['user_key']
        );
    }

    /** ------- HTTP firmado ------- */
    private function req(string $method, string $path, $body = null): array
    {
        $uri = $this->ctx->createAuthenticatedUri($path, $method);
        $ch  = curl_init($uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if (!is_null($body)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        $raw  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $json = $raw ? json_decode($raw, true) : null;
        return [$code, $json, $raw];
    }

    /** ------- Utilidades ------- */
    public function orgRootId(): ?int
    {
        [$c, $j] = $this->req('GET', "/d2l/api/lp/{$this->v}/organization/info");
        return $c === 200 ? (int)($j['Identifier'] ?? 0) ?: null : null;
    }

    public function rolesMap(): array
    {
        [$c, $j] = $this->req('GET', "/d2l/api/lp/{$this->vRoles}/roles/");
        $map = [];

        if ($c === 200 && is_array($j)) {
            foreach ($j as $role) {
                $id   = is_array($role) ? ($role['Id'] ?? $role['Identifier'] ?? null)
                                        : ($role->Id ?? $role->Identifier ?? null);
                $name = is_array($role) ? ($role['Name'] ?? $role['DisplayName'] ?? null)
                                        : ($role->Name ?? $role->DisplayName ?? null);
                if ($id !== null && $name !== null && $name !== '') {
                    $map[mb_strtolower(trim($name))] = (int)$id;
                }
            }
        }

        // Aliases convenientes
        $aliases = [
            'docente'    => ['docente','teacher','instructor','profesor'],
            'estudiante' => ['estudiante','student','learner','alumno'],
        ];
        foreach ($aliases as $canon => $vars) {
            foreach ($vars as $v) {
                $v = mb_strtolower($v);
                if (isset($map[$v])) { $map[$canon] = $map[$v]; break; }
            }
        }

        // Fallbacks por config
        if (!isset($map['docente']))    $map['docente']    = (int)(config('brightspace.teacher_role_id') ?? 109);
        if (!isset($map['estudiante'])) $map['estudiante'] = (int)(config('brightspace.default_role_id') ?? 110);

        return $map;
    }

    public function buscarOrgUnits(string $q, ?int $ouTypeId = null): array
    {
        $path = "/d2l/api/lp/{$this->v}/orgstructure/?search=" . rawurlencode($q);
        if ($ouTypeId) $path .= "&ouTypeId={$ouTypeId}";
        [$c, $j] = $this->req('GET', $path);
        return $c === 200 && is_array($j) ? $j : [];
    }

    public function ensureTemplate(string $code, string $name, int $parentId): ?int
    {
        // buscar por Code
        foreach ($this->buscarOrgUnits($code, null) as $ou) {
            if (($ou['Code'] ?? '') === $code) return (int)$ou['Identifier'];
        }
        // crear
        $payload = [
            "Name" => $name,
            "Code" => $code,
            "Path" => "",
            "ParentOrgUnitIds" => [$parentId]
        ];
        [$c, $j] = $this->req('POST', "/d2l/api/lp/{$this->v}/coursetemplates/", $payload);
        if ($c === 200) return (int)($j['Identifier'] ?? 0) ?: null;

        // reintento por si ya existía
        foreach ($this->buscarOrgUnits($code, null) as $ou) {
            if (($ou['Code'] ?? '') === $code) return (int)$ou['Identifier'];
        }
        return null;
    }

    public function ensureSemester(string $code, string $name, array $parentIds): ?int
    {
        $semesterTypeId = (int)(config('brightspace.semester_type_id') ?? 0);
        if (!$semesterTypeId) return null;

        // buscar por Code (filtrando por tipo si aplica)
        foreach ($this->buscarOrgUnits($code, $semesterTypeId) as $ou) {
            if (($ou['Code'] ?? '') === $code) return (int)$ou['Identifier'];
        }

        $payload = [
            "Type"    => $semesterTypeId,                         // número
            "Name"    => $name,
            "Code"    => $code,
            "Parents" => array_map('intval', $parentIds ?? [])    // números
        ];
        [$c, $j] = $this->req('POST', "/d2l/api/lp/{$this->v}/orgstructure/", $payload);
        if ($c === 200) return (int)($j['Identifier'] ?? 0) ?: null;

        // reintento búsqueda
        foreach ($this->buscarOrgUnits($code, $semesterTypeId) as $ou) {
            if (($ou['Code'] ?? '') === $code) return (int)$ou['Identifier'];
        }
        return null;
    }

    public function ensureOffering(string $code, string $name, int $templateId, ?int $semesterId): ?int
    {
        foreach ($this->buscarOrgUnits($code, null) as $ou) {
            if (($ou['Code'] ?? '') === $code) return (int)$ou['Identifier'];
        }

        $payload = [
            "Name"            => $name,
            "Code"            => $code,
            "Path"            => "",
            "CourseTemplateId"=> $templateId,
            "SemesterId"      => $semesterId ?: null,
            "StartDate"       => null,
            "EndDate"         => null,
            "LocaleId"        => null,
            "ForceLocale"     => true,
            "ShowAddressBook" => true,
            "Description"     => [ "Content" => "", "Type" => "Text" ],
            "CanSelfRegister" => false
        ];
        [$c, $j] = $this->req('POST', "/d2l/api/lp/{$this->v}/courses/", $payload);
        if ($c === 200) return (int)($j['Identifier'] ?? 0) ?: null;

        // reintento búsqueda
        foreach ($this->buscarOrgUnits($code, null) as $ou) {
            if (($ou['Code'] ?? '') === $code) return (int)$ou['Identifier'];
        }
        return null;
    }

    /** -------- Usuarios -------- */
    public function findUserIdByAll(?string $userName, ?string $orgDefinedId, ?string $email): ?int
    {
        $takeId = function ($j) {
            if (!$j) return null;
            if (is_array($j) && array_key_exists('Items', $j) && is_array($j['Items'])) {
                foreach ($j['Items'] as $it) {
                    if (is_array($it))  return $it['UserId'] ?? $it['Identifier'] ?? null;
                    if (is_object($it)) return $it->UserId ?? $it->Identifier ?? null;
                }
                return null;
            }
            if (is_array($j) && array_is_list($j)) {
                foreach ($j as $it) {
                    if (is_array($it))  return $it['UserId'] ?? $it['Identifier'] ?? null;
                    if (is_object($it)) return $it->UserId ?? $it->Identifier ?? null;
                }
                return null;
            }
            if (is_array($j))  return $j['UserId'] ?? $j['Identifier'] ?? null;
            if (is_object($j)) return $j->UserId ?? $j->Identifier ?? null;
            return null;
        };

        $call = function (string $qs) use ($takeId) {
            [$c, $j] = $this->req('GET', "/d2l/api/lp/{$this->v}/users/?{$qs}");
            return $c === 200 ? $takeId($j) : null;
        };

        if ($userName)   { $id = $call("userName="     . rawurlencode($userName));   if ($id) return (int)$id; }
        $org = $orgDefinedId ?: $userName;
        if ($org)        { $id = $call("orgDefinedId=" . rawurlencode($org));        if ($id) return (int)$id; }
        if ($email)      { $id = $call("externalEmail=". rawurlencode($email));      if ($id) return (int)$id; }

        foreach ([$userName, $org, $email] as $q) {
            if (!$q) continue;
            $id = $call("search=" . rawurlencode($q));
            if ($id) return (int)$id;
        }
        return null;
    }

    public function createUser(array $u): ?int
    {
        $payload = [
            "OrgDefinedId"      => $u['org_defined_id'] ?? $u['email'] ?? $u['usuario'],
            "FirstName"         => $u['nombres'],
            "MiddleName"        => "",
            "LastName"          => $u['apellidos'],
            "ExternalEmail"     => $u['email'] ?? null,
            "UserName"          => $u['usuario'],
            "RoleId"            => (int)$u['rol_id'],
            "IsActive"          => (bool)($u['activo'] ?? true),
            "SendCreationEmail" => false
        ];
        [$c, $j] = $this->req('POST', "/d2l/api/lp/{$this->v}/users/", $payload);
        if ($c === 201) return (int)($j['UserId'] ?? $j['Identifier'] ?? 0) ?: null;
        return null;
    }

    public function findOrCreateUser(array $u): ?int
    {
        $id = $this->findUserIdByAll($u['usuario'] ?? null, $u['org_defined_id'] ?? null, $u['email'] ?? null);
        if ($id) return $id;

        $id = $this->createUser($u);
        if ($id) return $id;

        // Reintento de búsqueda por si “ya existía”
        return $this->findUserIdByAll($u['usuario'] ?? null, $u['org_defined_id'] ?? null, $u['email'] ?? null);
    }

    public function enroll(int $orgUnitId, int $userId, int $roleId): bool
    {
        $payload = [ "OrgUnitId" => $orgUnitId, "UserId" => $userId, "RoleId" => $roleId ];
        [$c] = $this->req('POST', "/d2l/api/lp/{$this->v}/enrollments/", $payload);
        return ($c === 200 || $c === 204);
    }
}
