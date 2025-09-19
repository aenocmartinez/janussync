<?php

namespace App\Services\Brightspace;

class RoleResolver
{
    private \Closure $req;
    private string $vRoles;
    private array $cacheByCanonical = [];
    private array $fallbackIds;
    private array $aliases;

    public function __construct(\Closure $req, string $vRoles = '1.46')
    {
        $this->req     = $req;      // función ($method, $path, $body=null) -> [code, resp]
        $this->vRoles  = $vRoles;
        $cfg           = config('brightspace_roles');
        $this->aliases = array_map([$this, 'norm'], $cfg['aliases'] ?? []);
        $this->fallbackIds = $cfg['fallback_ids'] ?? ['docente' => 109, 'estudiante' => 110];
    }

    /** Normaliza: trim, lowercase, quita dobles espacios. */
    private function norm(string $s): string {
        $s = trim(mb_strtolower($s));
        $s = preg_replace('/\s+/u', ' ', $s);
        return $s;
    }

    /** Devuelve el nombre canónico a partir de un alias. */
    private function canonical(string $name): string {
        $n = $this->norm($name);
        // si está en aliases como key, retorna su valor; si no, retorna lo mismo
        foreach ($this->aliases as $alias => $canon) {
            if ($alias === $n) return $canon;
        }
        return $n;
    }

    /** Carga el mapa canónico->id desde la API /roles y cachea. */
    private function ensureCache(): void
    {
        if ($this->cacheByCanonical) return;

        $path = "/d2l/api/lp/{$this->vRoles}/roles/";
        [$c, $r] = ($this->req)('GET', $path);
        if ($c === 200 && $r) {
            $roles = json_decode($r, true);
            foreach ($roles as $role) {
                $display = $role['DisplayName'] ?? '';
                $id      = (int)($role['Identifier'] ?? 0);
                if ($display !== '' && $id > 0) {
                    $canon = $this->canonical($display);
                    $this->cacheByCanonical[$canon] = $id;
                }
            }
        }

        // si por alguna razón quedó vacío, aplica fallbacks
        if (!$this->cacheByCanonical) {
            foreach ($this->fallbackIds as $canon => $id) {
                $this->cacheByCanonical[$this->canonical($canon)] = (int)$id;
            }
        }
    }

    /** Obtiene el RoleId a partir de un nombre (alias o exacto). */
    public function id(string $roleName): ?int
    {
        $this->ensureCache();
        $canon = $this->canonical($roleName);

        // 1) búsqueda directa
        if (isset($this->cacheByCanonical[$canon])) {
            return $this->cacheByCanonical[$canon];
        }

        // 2) si llegó alias no listado, intenta el nombre literal normalizado
        return $this->cacheByCanonical[$canon] ?? null;
    }
}
