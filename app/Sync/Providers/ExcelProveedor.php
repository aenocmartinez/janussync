<?php

namespace App\Sync\Providers;

use App\Sync\Contracts\Proveedor;
use App\Sync\DTO\{PlantillaDTO, SemestreDTO, OfertaDTO, UsuarioDTO, InscripcionDTO};
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

final class ExcelProveedor implements Proveedor
{
    private ?Spreadsheet $ss = null;

    /** Ruta física del archivo Excel */
    protected string $ruta;

    public function __construct(string $rutaArchivo)
    {
        $this->ruta = $rutaArchivo;

        if (!is_file($rutaArchivo)) {
            throw new \InvalidArgumentException("No existe el archivo: {$rutaArchivo}");
        }

        // Carga del workbook
        $this->ss = IOFactory::load($rutaArchivo);
    }

    public function __destruct()
    {
        if ($this->ss instanceof \PhpOffice\PhpSpreadsheet\Spreadsheet) {
            // Desconectar para liberar memoria/handlers
            foreach ($this->ss->getAllSheets() as $sheet) {
                $sheet->disconnectCells();
            }
            $this->ss->disconnectWorksheets();
        }
        $this->ss = null;
    }    

    /** ================= API del Proveedor ================= */

    /**
     * Devuelve el mismo resumen que usamos en /sync/preview:
     *  - plantillas, semestres, ofertas, usuarios, inscripciones
     */
    public function previsualizar(): array
    {
        return $this->aModeloJanus();
    }

    /**
     * Alternativa estática por si quieres invocarlo sin instanciar antes.
     */
    public static function previsualizarArchivo(string $ruta): array
    {
        return (new self($ruta))->previsualizar();
    }

    /**
     * Construye el arreglo normalizado (arreglo de arreglos) a partir de las colecciones DTO.
     * Este formato es el que espera la ruta /sync/aplicar-prueba.
     */
    private function aModeloJanus(): array
    {
        // Plantillas
        $plantillas = $this->plantillas()
            ->map(function ($p) {
                // Intentamos propiedades típicas de un DTO
                $codigo = $p->codigo ?? $p->code ?? null;
                $nombre = $p->nombre ?? $p->name ?? $codigo;

                // Si el DTO tuviera toArray(), úsalo como respaldo
                if ($codigo === null && method_exists($p, 'toArray')) {
                    $arr = $p->toArray();
                    $codigo = $arr['codigo'] ?? $arr['code'] ?? null;
                    $nombre = $arr['nombre'] ?? $arr['name'] ?? $codigo;
                }

                return [
                    'codigo' => (string) $codigo,
                    'nombre' => (string) $nombre,
                ];
            })
            ->values()
            ->all();

        // Semestres
        $semestres = $this->semestres()
            ->map(function ($s) {
                $codigo = $s->codigo ?? $s->code ?? null;
                $nombre = $s->nombre ?? $s->name ?? $codigo;
                $ini    = $s->fechaInicio ?? $s->startDate ?? $s->start_date ?? null;
                $fin    = $s->fechaFin ?? $s->endDate ?? $s->end_date ?? null;

                if ($codigo === null && method_exists($s, 'toArray')) {
                    $arr    = $s->toArray();
                    $codigo = $arr['codigo'] ?? $arr['code'] ?? null;
                    $nombre = $arr['nombre'] ?? $arr['name'] ?? $codigo;
                    $ini    = $arr['fechaInicio'] ?? $arr['start_date'] ?? $ini;
                    $fin    = $arr['fechaFin'] ?? $arr['end_date'] ?? $fin;
                }

                return [
                    'codigo'      => (string) $codigo,
                    'nombre'      => (string) $nombre,
                    'fechaInicio' => $ini ?: null,
                    'fechaFin'    => $fin ?: null,
                ];
            })
            ->values()
            ->all();

        // Ofertas
        $ofertas = $this->ofertas()
            ->map(function ($o) {
                $codigo       = $o->codigo ?? $o->code ?? null;
                $nombre       = $o->nombre ?? $o->name ?? $codigo;
                $codPlantilla = $o->codigoPlantilla ?? $o->template_code ?? null;
                $codSemestre  = $o->codigoSemestre ?? $o->semester_code ?? null;

                if ($codigo === null && method_exists($o, 'toArray')) {
                    $arr         = $o->toArray();
                    $codigo       = $arr['codigo'] ?? $arr['code'] ?? null;
                    $nombre       = $arr['nombre'] ?? $arr['name'] ?? $codigo;
                    $codPlantilla = $arr['codigoPlantilla'] ?? $arr['template_code'] ?? null;
                    $codSemestre  = $arr['codigoSemestre'] ?? $arr['semester_code'] ?? null;
                }

                return [
                    'codigo'         => (string) $codigo,
                    'nombre'         => (string) $nombre,
                    'codigoPlantilla'=> (string) $codPlantilla,
                    'codigoSemestre' => (string) $codSemestre,
                ];
            })
            ->values()
            ->all();

        // Usuarios
        $usuarios = $this->usuarios()
            ->map(function ($u) {
                $idAs     = $u->idAcademusoft ?? $u->orgDefinedId ?? $u->org_defined_id ?? null;
                $nombres  = $u->nombres ?? $u->firstName ?? $u->first_name ?? '';
                $apellidos= $u->apellidos ?? $u->lastName ?? $u->last_name ?? '';
                $usuario  = $u->usuario ?? $u->username ?? null;
                $email    = $u->email ?? null;
                $rol      = $u->rol ?? $u->role ?? 'ESTUDIANTE';
                $activo   = isset($u->activo) ? (bool)$u->activo : (isset($u->is_active) ? (bool)$u->is_active : true);

                if ($idAs === null && method_exists($u, 'toArray')) {
                    $arr      = $u->toArray();
                    $idAs     = $arr['idAcademusoft'] ?? $arr['org_defined_id'] ?? null;
                    $nombres  = $arr['nombres'] ?? $arr['first_name'] ?? $nombres;
                    $apellidos= $arr['apellidos'] ?? $arr['last_name'] ?? $apellidos;
                    $usuario  = $arr['usuario'] ?? $arr['username'] ?? $usuario;
                    $email    = $arr['email'] ?? $email;
                    $rol      = $arr['rol'] ?? $rol;
                    $activo   = $arr['activo'] ?? $activo;
                }

                return [
                    'idAcademusoft' => (string) $idAs,
                    'nombres'       => (string) $nombres,
                    'apellidos'     => (string) $apellidos,
                    'usuario'       => (string) $usuario,
                    'email'         => $email ?: null,
                    'rol'           => (string) strtoupper($rol),
                    'activo'        => (bool) $activo,
                ];
            })
            ->values()
            ->all();

        // Inscripciones
        $inscripciones = $this->inscripciones()
            ->map(function ($i) {
                $idAsUsuario = $i->idAcademusoftUsuario ?? $i->child ?? $i->child_code ?? null;
                $codigoOferta= $i->codigoOferta ?? $i->oferta ?? $i->parent_code ?? null;
                $rol         = $i->rol ?? $i->role ?? $i->role_name ?? 'ESTUDIANTE';

                if ($idAsUsuario === null && method_exists($i, 'toArray')) {
                    $arr         = $i->toArray();
                    $idAsUsuario = $arr['idAcademusoftUsuario'] ?? $arr['child_code'] ?? null;
                    $codigoOferta= $arr['codigoOferta'] ?? $arr['parent_code'] ?? null;
                    $rol         = $arr['rol'] ?? $arr['role_name'] ?? $rol;
                }

                return [
                    'idAcademusoftUsuario' => (string) $idAsUsuario,   // aquí va username/orgDefinedId según tu Excel
                    'codigoOferta'         => (string) $codigoOferta,  // igual al code de la hoja Cursos
                    'rol'                  => (string) strtoupper($rol),
                ];
            })
            ->values()
            ->all();

        return compact('plantillas', 'semestres', 'ofertas', 'usuarios', 'inscripciones');
    }

    /** @return Collection<PlantillaDTO> */
    public function plantillas(): Collection
    {
        // Hoja: "2 plantillas", headers: type, action, code, name, ...
        $rows = $this->leerHojaDatos(
            ['2 plantillas','plantillas'],
            ['code','name'] // tokens mínimos
        );

        return collect($rows)->map(function ($r) {
            $codigo = $this->txt($r, ['code']);
            $nombre = $this->txt($r, ['name']);
            if ($codigo === '') return null;
            return new PlantillaDTO($codigo, $nombre ?: $codigo);
        })->whereNotNull()->values();
    }

    /** @return Collection<SemestreDTO> */
    public function semestres(): Collection
    {
        // Hoja: "3 semestres", headers: code, name, start_date, end_date, ...
        $rows = $this->leerHojaDatos(
            ['3 semestres','semestres'],
            ['code','name','start_date','end_date']
        );

        return collect($rows)->map(function ($r) {
            $codigo = $this->txt($r, ['code']);
            $nombre = $this->txt($r, ['name']) ?: $codigo;
            $ini    = $this->txt($r, ['start_date']);
            $fin    = $this->txt($r, ['end_date']);
            if ($codigo === '') return null;
            return new SemestreDTO($codigo, $nombre, $ini ?: null, $fin ?: null);
        })->whereNotNull()->values();
    }

    /** @return Collection<OfertaDTO> */
    public function ofertas(): Collection
    {
        // Hoja: "4 cursos", headers: code, name, template_code, semester_code, ...
        $rows = $this->leerHojaDatos(
            ['4 cursos','cursos','ofertas'],
            ['code','name','template_code','semester_code']
        );

        return collect($rows)->map(function ($r) {
            $codigo       = $this->txt($r, ['code']);
            $nombre       = $this->txt($r, ['name']) ?: $codigo;
            $codPlantilla = $this->txt($r, ['template_code']);
            $codSemestre  = $this->txt($r, ['semester_code']);
            if ($codigo === '' || $codPlantilla === '' || $codSemestre === '') return null;
            return new OfertaDTO($codigo, $nombre, $codPlantilla, $codSemestre);
        })->whereNotNull()->values();
    }

    /** @return Collection<UsuarioDTO> */
    public function usuarios(): Collection
    {
        // Hoja: "5 usuarios", headers: username, org_defined_id, first_name, last_name, is_active, role_name, email, ...
        $rows = $this->leerHojaDatos(
            ['5 usuarios','usuarios'],
            ['username','org_defined_id','first_name','last_name','role_name','email','is_active']
        );

        return collect($rows)->map(function ($r) {
            $idAs   = $this->txt($r, ['org_defined_id']);  // ancla (Academusoft)
            $user   = $this->txt($r, ['username']);        // p.ej. correo
            $nombre = $this->txt($r, ['first_name']);
            $ape    = $this->txt($r, ['last_name']);
            $email  = $this->txt($r, ['email']);

            // Rol: "Docente"/"Estudiante" -> estandarizar
            $rolRaw = strtoupper($this->txt($r, ['role_name']));
            $rol    = ($rolRaw === 'DOCENTE') ? 'DOCENTE' : 'ESTUDIANTE';

            // Activo: TRUE/1/yes -> bool
            $actRaw = strtoupper($this->txt($r, ['is_active']));
            $activo = in_array($actRaw, ['TRUE','1','YES','SI','SÍ'], true);

            // Si viniera vacío org_defined_id, usa username como fallback (temporal)
            if ($idAs === '') $idAs = $user;

            if ($idAs === '' || $user === '') return null;

            return new UsuarioDTO($idAs, $nombre, $ape, strtolower($user), $email ?: null, $rol, $activo);
        })->whereNotNull()->values();
    }

    /** @return Collection<InscripcionDTO> */
    public function inscripciones(): Collection
    {
        // Hoja: "6 inscripciones", headers: child_code (usuario), role_name, parent_code (curso)
        $rows = $this->leerHojaDatos(
            ['6 inscripciones','inscripciones'],
            ['child_code','role_name','parent_code']
        );

        return collect($rows)->map(function ($r) {
            $child = $this->txt($r, ['child_code']);  // en tu Excel es el username (correo)
            $rolRaw= strtoupper($this->txt($r, ['role_name']));
            $rol   = ($rolRaw === 'DOCENTE') ? 'DOCENTE' : 'ESTUDIANTE';
            $oferta= $this->txt($r, ['parent_code']); // coincide con "code" de la hoja Cursos

            if ($child === '' || $oferta === '') return null;

            // Guardamos el username en el campo "idAcademusoftUsuario".
            return new InscripcionDTO($child, $oferta, $rol);
        })->whereNotNull()->values();
    }

    /** ================= Utilidades internas ================= */

    /**
     * Lee una hoja buscando por nombre "borroso" y detecta el encabezado correcto.
     * - $nombresCandidatos: posibles nombres (ej. '2 plantillas','plantillas')
     * - $tokensEsperados: nombres de columnas que esperas encontrar (normalizados)
     */
    private function leerHojaDatos(array $nombresCandidatos, array $tokensEsperados, int $minCols = 2): array
    {
        $sheet = $this->buscarHoja($nombresCandidatos);
        if (!$sheet) return [];

        $rows = $sheet->toArray(null, true, true, true);
        if (empty($rows)) return [];

        // 1) Buscar fila de encabezados
        $headerRowIndex = null;
        $headerKeys = null;

        // Pre-normaliza tokens esperados
        $tokensNorm = array_map([$this,'normalizaHeader'], $tokensEsperados);

        foreach ($rows as $idx => $row) {
            $nnz = 0;
            $rowNorm = [];
            foreach ($row as $cell) {
                $cellStr = ($cell === null) ? '' : trim((string)$cell);
                if ($cellStr !== '') $nnz++;
                $rowNorm[] = $this->normalizaHeader($cellStr);
            }

            $contieneToken = count(array_intersect($rowNorm, $tokensNorm)) > 0;

            if ($nnz >= $minCols || $contieneToken) {
                // construir headers (si alguno es vacío -> col_N)
                $headers = [];
                $colNum = 0;
                foreach ($row as $cell) {
                    $key = $this->normalizaHeader(($cell === null) ? '' : (string)$cell);
                    if ($key === '') $key = 'col_' . (++$colNum);
                    $headers[] = $key;
                }
                $headerRowIndex = $idx;
                $headerKeys = $headers;
                break;
            }
        }

        if ($headerRowIndex === null || empty($headerKeys)) {
            return [];
        }

        // 2) Convertir filas siguientes a arreglos asociativos con esos headers
        $rowIndexes = array_keys($rows);
        sort($rowIndexes, SORT_NUMERIC);

        $data = [];
        $startPos = array_search($headerRowIndex, $rowIndexes, true);

        for ($p = $startPos + 1; $p < count($rowIndexes); $p++) {
            $rowIdx = $rowIndexes[$p];
            $cells  = array_values($rows[$rowIdx] ?? []);
            $assoc  = [];

            $max = count($headerKeys);
            for ($i = 0; $i < $max; $i++) {
                $assoc[$headerKeys[$i]] = isset($cells[$i]) ? trim((string)$cells[$i]) : '';
            }

            // descartar filas totalmente vacías
            if (implode('', array_values($assoc)) === '') continue;

            $data[] = $assoc;
        }

        return $data;
    }

    /** Devuelve la hoja que mejor matchee con los candidatos (normalizados). */
    private function buscarHoja(array $candidatos)
    {
        $cands = array_map([$this,'normalizaNombreHoja'], $candidatos);

        $sheetCount = $this->ss->getSheetCount();
        for ($i = 0; $i < $sheetCount; $i++) {
            $name = $this->ss->getSheetNames()[$i] ?? '';
            $norm = $this->normalizaNombreHoja($name);
            foreach ($cands as $c) {
                if ($norm === $c || str_contains($norm, $c)) {
                    return $this->ss->getSheet($i);
                }
            }
        }
        return null;
    }

    /** Normaliza headers (y cadenas en general) */
    private function normalizaHeader($h): string
    {
        if ($h === null) return '';
        $h = (string) $h;
        $h = trim(mb_strtolower($h));
        $h = strtr($h, [
            'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
            'ä'=>'a','ë'=>'e','ï'=>'i','ö'=>'o','ü'=>'u','ñ'=>'n'
        ]);
        $h = preg_replace('/[^a-z0-9]+/u', '_', $h);
        $h = preg_replace('/_+/', '_', $h);
        return trim($h, '_');
    }

    /** Normaliza nombres de hojas (más permisivo aún) */
    private function normalizaNombreHoja(string $s): string
    {
        $s = $this->normalizaHeader($s);
        // quitar prefijos muy comunes como 'hoja_', 'sheet_'
        $s = preg_replace('/^(hoja|sheet)_*/', '', $s);
        // compactar números con guiones: '2-plantillas' => '2_plantillas'
        $s = preg_replace('/-+/', '_', $s);
        return $s;
    }

    /** Primer valor no vacío entre posibles nombres de columna */
    private function txt(array $row, array $posibles): string
    {
        foreach ($posibles as $k) {
            $kNorm = $this->normalizaHeader($k);
            if (array_key_exists($kNorm, $row) && trim((string)$row[$kNorm]) !== '') {
                return trim((string)$row[$kNorm]);
            }
        }
        return '';
    }
}
