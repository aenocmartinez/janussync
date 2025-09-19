<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SyncExcelController extends Controller
{
    private string $nombreFijo = 'CARGUE MASIVO BRIGHTSPACE.xlsx';

    public function index(Request $request)
    {
        $ruta   = Storage::path('imports/'.$this->nombreFijo);
        $existe = file_exists($ruta);

        $preview = null;
        if ($existe) {
            try {
                $prov    = new \App\Sync\Providers\ExcelProveedor($ruta);
                $preview = $prov->previsualizar();
            } catch (\Throwable $e) {
                $preview = ['ok'=>false, 'error'=>'No se pudo previsualizar el Excel: '.$e->getMessage()];
            }
        }

        return view('sync.excel', [
            'archivo' => $this->nombreFijo,
            'ruta'    => $ruta,
            'existe'  => $existe,
            'preview' => $preview,
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'archivo' => ['required','file','mimes:xlsx,xls','max:10240'],
        ]);

        Storage::makeDirectory('imports');
        Storage::putFileAs('imports', $request->file('archivo'), $this->nombreFijo);

        return back()->with('success', 'Archivo cargado correctamente.');
    }

    public function apply(Request $request)
    {
        $request->validate([
            'confirmar' => ['accepted'],
        ], [
            'confirmar.accepted' => 'Debes confirmar que deseas aplicar los cambios.',
        ]);

        $ruta = Storage::path('imports/'.$this->nombreFijo);
        if (!file_exists($ruta)) {
            return back()->with('error', 'No existe el archivo en: '.$ruta);
        }

        try {
            $resultado = $this->aplicarDesdeExcel($ruta);
        } catch (\Throwable $e) {
            $resultado = ['ok'=>false, 'error'=>'Excepción durante sincronización: '.$e->getMessage()];
        }

        if ($resultado['ok'] ?? false) {
            session()->flash('success', 'Sincronización ejecutada.');
        } else {
            session()->flash('error', $resultado['error'] ?? 'Falló la sincronización.');
        }

        return back()->with('resultado_sync', $resultado);
    }

    /** === Lógica principal (idéntica a la que ya probaste) === */
private function aplicarDesdeExcel(string $archivo): array
{
    $prov = new \App\Sync\Providers\ExcelProveedor($archivo);
    $prev = $prov->previsualizar();

    if (empty($prev['plantillas']) || empty($prev['semestres']) || empty($prev['ofertas'])) {
        return [
            'ok' => false,
            'error' => 'El Excel no tiene datos suficientes (plantillas/semestres/ofertas).',
            'conteo' => [
                'plantillas'    => count($prev['plantillas']    ?? []),
                'semestres'     => count($prev['semestres']     ?? []),
                'ofertas'       => count($prev['ofertas']       ?? []),
                'usuarios'      => count($prev['usuarios']      ?? []),
                'inscripciones' => count($prev['inscripciones'] ?? []),
            ]
        ];
    }

    $cfg = config('brightspace');
    $host   = $cfg['host'];    $port = $cfg['port'];   $scheme = $cfg['scheme'];
    $appId  = $cfg['app_id'];  $appKey = $cfg['app_key'];
    $uid    = $cfg['user_id']; $ukey   = $cfg['user_key'];
    $lib    = $cfg['libpath'];

    $v       = '1.43'; // LP API principal
    $v_roles = '1.46'; // LP roles

    require_once $lib.'/D2LAppContextFactory.php';
    require_once $lib.'/D2LHostSpec.php';

    $ctx = (new \D2LAppContextFactory())
        ->createSecurityContext($appId, $appKey)
        ->createUserContextFromHostSpec(new \D2LHostSpec($host, $port, $scheme), $uid, $ukey);

    // === Helper HTTP con Accept: application/json (errores claros)
    $req = function (string $method, string $path, $body = null) use ($ctx) {
        $uri = $ctx->createAuthenticatedUri($path, $method);
        $ch  = curl_init($uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $headers = ['Accept: application/json'];
        if (!is_null($body)) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [$code, $resp];
    };
    $json = fn($s) => $s ? json_decode($s, true) : null;

    $DBG = [];

    // === Utilidades locales ===
    $fitCode = function (string $s): string {
        $s = trim($s ?? '');
        return mb_substr($s, 0, 50); // muchos tenants exigen <= 50
    };

    $norm = function (?string $s): string {
        $s = (string)($s ?? '');
        $s = trim(mb_strtolower($s));
        return preg_replace('/\s+/u', ' ', $s);
    };

    // UserName = parte local del correo (sin @dominio)
    $usernameLocal = function (?string $u): string {
        $u = (string)($u ?? '');
        $u = trim($u);
        if ($u === '') return '';
        $at = strpos($u, '@');
        return $at === false ? $u : substr($u, 0, $at);
    };

    // ===== Helpers de Brightspace =====
    $orgInfo = function () use ($req, $v, $json) {
        [$c, $r] = $req('GET', "/d2l/api/lp/{$v}/organization/info");
        if ($c === 200) { $o = $json($r); return $o['Identifier'] ?? null; }
        return null;
    };

    $buscarOrgUnits = function (string $q, ?int $ouTypeId = null) use ($req, $v, $json) {
        $path = "/d2l/api/lp/{$v}/orgstructure/?search=" . rawurlencode($q);
        if ($ouTypeId) $path .= "&ouTypeId={$ouTypeId}";
        [$c, $r] = $req('GET', $path);
        return $c === 200 ? $json($r) : [];
    };

    $resolverSemesterTypeId = fn() => (int) (config('brightspace.semester_type_id') ?? 0) ?: null;

    $asegurarOrgUnit = function (array $data) use ($req, $v, $json, $buscarOrgUnits, $resolverSemesterTypeId, &$DBG) {
        $exist = $buscarOrgUnits($data['codigo'], null);
        foreach ($exist as $ou) {
            if (($ou['Code'] ?? '') === $data['codigo']) {
                return $ou['Identifier'];
            }
        }

        if ($data['tipo'] === 'coursetemplate') {
            $payload = [
                "Name" => $data['nombre'],
                "Code" => $data['codigo'],
                "Path" => "",
                "ParentOrgUnitIds" => $data['padres'] ?? []
            ];
            [$c, $r] = $req('POST', "/d2l/api/lp/{$v}/coursetemplates/", $payload);
            if ($c === 200) { $o = $json($r); return $o['Identifier'] ?? null; }
            $DBG['template']['create'] = ['http'=>$c, 'resp'=>$r, 'payload'=>$payload];
            $exist2 = $buscarOrgUnits($data['codigo'], null);
            foreach ($exist2 as $ou) {
                if (($ou['Code'] ?? '') === $data['codigo']) return $ou['Identifier'];
            }
            return null;
        }

        if ($data['tipo'] === 'semester') {
            $semesterTypeId = $resolverSemesterTypeId();
            $existS = $buscarOrgUnits($data['codigo'], $semesterTypeId ?: null);
            foreach ($existS as $ou) {
                if (($ou['Code'] ?? '') === $data['codigo']) return $ou['Identifier'];
            }

            if (!$semesterTypeId) {
                $DBG['semester']['error'] = 'semester_type_id vacío en config';
                return null;
            }

            $payload = [
                "Type"    => (int) $semesterTypeId,
                "Name"    => $data['nombre'],
                "Code"    => $data['codigo'],
                "Parents" => array_map('intval', $data['padres'] ?? [])
            ];
            [$c, $r] = $req('POST', "/d2l/api/lp/{$v}/orgstructure/", $payload);
            if ($c === 200) { $o = $json($r); return $o['Identifier'] ?? null; }

            $DBG['semester']['create'] = ['http'=>$c, 'resp'=>$r, 'payload'=>$payload];
            return null;
        }

        return null;
    };

    // Creador de oferta: intenta Code original; si 400, reintenta con Code <= 50
    $crearOferta = function (array $d) use ($req, $v, $json, $buscarOrgUnits, $fitCode, &$DBG) {
        $exist = $buscarOrgUnits($d['codigo'], null);
        foreach ($exist as $ou) {
            if (($ou['Code'] ?? '') === $d['codigo']) return $ou['Identifier'];
        }

        $payload = [
            "Name" => $d['nombre'],
            "Code" => $d['codigo'], // intento 1: tal cual
            "Path" => "",
            "CourseTemplateId" => (int)$d['templateId'],
            "SemesterId" => $d['semesterId'] ? (int)$d['semesterId'] : null,
            "StartDate" => null,
            "EndDate" => null,
            "LocaleId" => null,
            "ForceLocale" => true,
            "ShowAddressBook" => true,
            "Description" => [ "Content" => "", "Type" => "Text" ],
            "CanSelfRegister" => false
        ];
        [$c, $r] = $req('POST', "/d2l/api/lp/{$v}/courses/", $payload);
        if ($c === 200) { $o = $json($r); return $o['Identifier'] ?? null; }

        if ($c === 400) { // reintento con code recortado
            $payloadRetry = $payload;
            $payloadRetry['Code'] = $fitCode($payload['Code']);
            [$c2, $r2] = $req('POST', "/d2l/api/lp/{$v}/courses/", $payloadRetry);
            if ($c2 === 200) { $o = $json($r2); return $o['Identifier'] ?? null; }
            $DBG['offering']['create'][] = ['http'=>$c,  'resp'=>$r,  'payload'=>$payload];
            $DBG['offering']['retry'][]  = ['http'=>$c2, 'resp'=>$r2, 'payload'=>$payloadRetry];
            return null;
        }

        $DBG['offering']['create'][] = ['http'=>$c, 'resp'=>$r, 'payload'=>$payload];
        return null;
    };

    // === Usuarios ===
    // Búsqueda ESTRICTA: solo orgDefinedId y userName exacto (sin /search)
    $buscarUsuario = function (array $u) use ($req, $v, $json, $usernameLocal, &$DBG) {
        $firstUserId = function ($resp) {
            if (!$resp) return null;
            if (isset($resp['Items'][0]['UserId'])) return (int)$resp['Items'][0]['UserId'];
            if (isset($resp[0]['UserId']))          return (int)$resp[0]['UserId'];
            return null;
        };

        // 1) orgDefinedId
        $org = $u['org_defined_id'] ?? $u['idAcademusoft'] ?? $u['orgDefinedId'] ?? null;
        if (!empty($org)) {
            [$c, $r] = $req('GET', "/d2l/api/lp/{$v}/users/?orgDefinedId=" . rawurlencode((string)$org));
            if ($c === 200) {
                $id = $firstUserId($json($r));
                if ($id) return $id;
            }
            $DBG['user']['lookup'][] = ['qs' => "orgDefinedId={$org}", 'http' => $c ?? null];
        }

        // 2) userName exacto (si viene correo, usar parte local)
        $uname = $u['usuario'] ?? $u['username'] ?? $u['email'] ?? null;
        $uname = $usernameLocal($uname);
        if ($uname !== '') {
            [$c, $r] = $req('GET', "/d2l/api/lp/{$v}/users/?userName=" . rawurlencode($uname));
            if ($c === 200) {
                $id = $firstUserId($json($r));
                if ($id) return $id;
            }
            $DBG['user']['lookup'][] = ['qs' => "userName={$uname}", 'http' => $c ?? null];
        }

        return null; // SIN /search para evitar colisiones
    };

    // Crear usuario si no existe (UserName = parte local del correo)
    $crearUsuarioSiNoExiste = function (array $u) use ($req, $v, $json, $buscarUsuario, $usernameLocal, &$DBG) {
        $id = $buscarUsuario($u);
        if ($id) return $id;

        $orgDef = $u['org_defined_id'] ?? $u['idAcademusoft'] ?? null;
        $uname  = $usernameLocal($u['usuario'] ?? $u['username'] ?? $u['email'] ?? null);

        $payload = [
            "OrgDefinedId"      => $orgDef ?: ($u['email'] ?? $uname ?: null),
            "FirstName"         => $u['nombres']  ?? $u['first_name'] ?? '',
            "MiddleName"        => "",
            "LastName"          => $u['apellidos'] ?? $u['last_name']  ?? '',
            "ExternalEmail"     => $u['email'] ?? null,
            "UserName"          => $uname, // SIN dominio
            "RoleId"            => (int)$u['rol_id'],
            "IsActive"          => (bool)($u['activo'] ?? true),
            "SendCreationEmail" => false
        ];

        [$c, $r] = $req('POST', "/d2l/api/lp/{$v}/users/", $payload);
        if ($c === 201) {
            $o = $json($r);
            return $o['UserId'] ?? null;
        }

        // Si 400 “ya existe”, re-buscar estrictamente por orgDefinedId y userName
        if ($c === 400) {
            if (!empty($orgDef)) {
                [$c2, $r2] = $req('GET', "/d2l/api/lp/{$v}/users/?orgDefinedId=" . rawurlencode((string)$orgDef));
                if ($c2 === 200) {
                    $resp = $json($r2);
                    if (isset($resp['Items'][0]['UserId'])) return (int)$resp['Items'][0]['UserId'];
                }
            }
            if ($uname !== '') {
                [$c3, $r3] = $req('GET', "/d2l/api/lp/{$v}/users/?userName=" . rawurlencode($uname));
                if ($c3 === 200) {
                    $resp = $json($r3);
                    if (isset($resp['Items'][0]['UserId'])) return (int)$resp['Items'][0]['UserId'];
                }
            }
        }

        $DBG['user']['create'][] = ['http'=>$c, 'resp'=>$r, 'payload'=>$payload];
        return null;
    };

    // ===== Roles (con alias + cache) =====
    $alias = [
        'docente'    => 'docente',
        'profesor'   => 'docente',
        'instructor' => 'docente',
        'teacher'    => 'docente',
        'estudiante' => 'estudiante',
        'alumno'     => 'estudiante',
        'student'    => 'estudiante',
    ];
    $roleCacheByCanonical = [];
    $resolveRoleId = function (string $name) use ($req, $v_roles, $json, $norm, $alias, &$roleCacheByCanonical) {
        $canon = $alias[$norm($name)] ?? $norm($name);
        if (isset($roleCacheByCanonical[$canon])) return $roleCacheByCanonical[$canon];

        [$c, $r] = $req('GET', "/d2l/api/lp/{$v_roles}/roles/");
        if ($c === 200 && $r) {
            $roles = $json($r);
            foreach ($roles as $role) {
                $display = $role['DisplayName'] ?? '';
                $id      = (int)($role['Identifier'] ?? 0);
                if ($display !== '' && $id > 0) {
                    $canonName = $alias[$norm($display)] ?? $norm($display);
                    $roleCacheByCanonical[$canonName] = $id;
                }
            }
        }
        if (!isset($roleCacheByCanonical['docente']))    $roleCacheByCanonical['docente']    = (int)(config('brightspace.teacher_role_id') ?? 109);
        if (!isset($roleCacheByCanonical['estudiante'])) $roleCacheByCanonical['estudiante'] = (int)(config('brightspace.default_role_id') ?? 110);
        return $roleCacheByCanonical[$canon] ?? null;
    };

    // ===== Estado/índices =====
    $ids = [
        'templateId' => null,
        'semesterId' => null,
        'offerings'  => [],
        'usuarios'   => [],
        'inscritos'  => [],
    ];
    $userIndex = [
        'byOrg'   => [], // OrgDefinedId -> UserId
        'byUser'  => [], // UserName     -> UserId
        'byEmail' => [], // Email        -> UserId
    ];

    // ===== Flujo principal =====
    $orgRootId = $orgInfo();
    if (!$orgRootId) return ['ok'=>false,'error'=>'No se pudo obtener el OrgRootId de Brightspace.'];

    $plant = $prev['plantillas'][0];
    $ids['templateId'] = $asegurarOrgUnit([
        'tipo'   => 'coursetemplate',
        'codigo' => $plant['codigo'],
        'nombre' => $plant['nombre'],
        'padres' => [$orgRootId]
    ]);
    if (!$ids['templateId']) return ['ok'=>false,'error'=>'No se pudo asegurar la Plantilla en Brightspace.','debug'=>$DBG];

    $sem = $prev['semestres'][0];
    $ids['semesterId'] = $asegurarOrgUnit([
        'tipo'   => 'semester',
        'codigo' => $sem['codigo'],
        'nombre' => $sem['nombre'],
        'padres' => [$orgRootId]
    ]);
    if (!$ids['semesterId']) return ['ok'=>false,'error'=>'No se pudo asegurar el Semestre.','debug'=>$DBG];

    foreach ($prev['ofertas'] as $of) {
        $offId = $crearOferta([
            'codigo'     => $of['codigo'],           // si 400, reintenta con <=50
            'nombre'     => $of['nombre'],
            'templateId' => $ids['templateId'],
            'semesterId' => $ids['semesterId'],
        ]);
        if ($offId) $ids['offerings'][$of['codigo']] = $offId;
    }

    // Crear/Resolver usuarios (sin /search, usando OrgDefinedId/UserName)
    foreach ($prev['usuarios'] as $u) {
        $rolNombre = $u['rol'] ?? ($u['role_name'] ?? 'estudiante');
        $rolId     = $resolveRoleId($rolNombre) ?? 110;

        $userId = $crearUsuarioSiNoExiste([
            'org_defined_id' => $u['idAcademusoft'] ?? $u['org_defined_id'] ?? null,
            'nombres'        => $u['nombres']       ?? $u['first_name']     ?? '',
            'apellidos'      => $u['apellidos']     ?? $u['last_name']      ?? '',
            'usuario'        => $u['usuario']       ?? $u['username']       ?? ($u['email'] ?? null),
            'email'          => $u['email']         ?? null,
            'rol_id'         => $rolId,
            'activo'         => $u['activo']        ?? true,
        ]);

        if ($userId) {
            $orgKey = (string)($u['idAcademusoft'] ?? $u['org_defined_id'] ?? '');
            if ($orgKey !== '') {
                $ids['usuarios'][$orgKey] = $userId;
                $userIndex['byOrg'][$orgKey] = $userId;
            }
            $uname = $u['usuario'] ?? $u['username'] ?? null;
            if (!empty($uname)) {
                $k = mb_strtolower(trim((string)$usernameLocal($uname)));
                if ($k !== '') $userIndex['byUser'][$k] = $userId;
            }
            if (!empty($u['email'])) {
                $k = mb_strtolower(trim((string)$u['email']));
                if ($k !== '') $userIndex['byEmail'][$k] = $userId;
            }
        }
    }

    // Resolver usuario para inscripción: SOLO por OrgDefinedId (como en tu validación manual)
    $resolveUserIdForEnrollment = function (array $row) use ($ids, $userIndex, $buscarUsuario) {
        $org = $row['idAcademusoftUsuario'] ?? $row['child_code'] ?? null; // child_code suele ser orgDefinedId
        if (!empty($org)) {
            $key = (string)$org;
            if (isset($ids['usuarios'][$key]))   return $ids['usuarios'][$key];
            if (isset($userIndex['byOrg'][$key])) return $userIndex['byOrg'][$key];

            // Búsqueda estricta por OrgDefinedId
            $found = $buscarUsuario(['org_defined_id' => $key]);
            if ($found) return $found;
        }

        // Sin fallback por email/username/search
        return null;
    };

    // Inscripciones
    foreach ($prev['inscripciones'] as $row) {
        $codigoOferta = $row['codigoOferta'] ?? ($row['parent_code'] ?? null);
        $ofId = $codigoOferta ? ($ids['offerings'][$codigoOferta] ?? null) : null;

        $uId = $resolveUserIdForEnrollment($row);

        $rolNombre = $row['rol'] ?? ($row['role_name'] ?? 'estudiante');
        $rolId     = $resolveRoleId($rolNombre) ?? 110;

        $ok = ($ofId && $uId) ? (function () use ($ofId, $uId, $rolId, $req, $v, &$DBG) {
            $payload = [ "OrgUnitId" => (int)$ofId, "UserId" => (int)$uId, "RoleId" => (int)$rolId ];
            [$c, $r] = $req('POST', "/d2l/api/lp/{$v}/enrollments/", $payload);
            if ($c === 200 || $c === 204) return true;
            $DBG['enrollment']['create'][] = ['http'=>$c, 'resp'=>$r, 'payload'=>$payload];
            return false;
        })() : false;

        $motivo = null;
        if (!$ofId) $motivo = 'oferta_no_resuelta';
        elseif (!$uId) $motivo = 'usuario_no_resuelto';

        $ids['inscritos'][] = [
            'oferta' => $codigoOferta,
            'user'   => $row['idAcademusoftUsuario'] ?? ($row['child_code'] ?? null),
            'rol'    => mb_strtolower((string)$rolNombre),
            'ok'     => $ok,
            'motivo' => $ok ? null : $motivo,
        ];
    }

    return ['ok'=>true, 'ids'=>$ids, 'debug'=>$DBG];
}



}
