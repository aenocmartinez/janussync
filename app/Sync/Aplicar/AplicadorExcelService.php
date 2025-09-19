<?php

namespace App\Sync\Aplicar;

use App\Sync\Providers\ExcelProveedor;
use App\Sync\Brightspace\BrightspaceClient;

final class AplicadorExcelService
{
    private ExcelProveedor $prov;
    private BrightspaceClient $bs;

    public function __construct(ExcelProveedor $prov, BrightspaceClient $bs)
    {
        $this->prov = $prov;
        $this->bs   = $bs;
    }

    /**
     * Ejecuta el flujo: Plantilla -> Semestre -> Ofertas -> Usuarios -> Inscripciones
     * Devuelve el mismo shape que ya validaste en la ruta.
     */
    public function aplicar(): array
    {
        // 1) Previsualizar (DTOs normalizados del Excel)
        $prev = $this->prov->previsualizar();

        if (empty($prev['plantillas']) || empty($prev['semestres']) || empty($prev['ofertas'])) {
            return [
                'ok' => false,
                'error' => 'El Excel no tiene datos suficientes (plantillas/semestres/ofertas).',
                'conteo' => [
                    'plantillas' => count($prev['plantillas'] ?? []),
                    'semestres'  => count($prev['semestres']  ?? []),
                    'ofertas'    => count($prev['ofertas']    ?? []),
                    'usuarios'   => count($prev['usuarios']   ?? []),
                    'inscripciones' => count($prev['inscripciones'] ?? []),
                ]
            ];
        }

        // 2) Contexto Brightspace
        $root = $this->bs->orgRootId();
        if (!$root) {
            return ['ok'=>false,'error'=>'No se pudo obtener OrgRootId de Brightspace.'];
        }
        $roles = $this->bs->rolesMap();

        // 3) Asegurar Plantilla y Semestre
        $plant = $prev['plantillas'][0];
        $tplId = $this->bs->ensureTemplate($plant['codigo'], $plant['nombre'], (int)$root);
        if (!$tplId) return ['ok'=>false,'error'=>'No se pudo asegurar la Plantilla.'];

        $sem   = $prev['semestres'][0];
        $semId = $this->bs->ensureSemester($sem['codigo'], $sem['nombre'], [(int)$root]);
        if (!$semId) return ['ok'=>false,'error'=>'No se pudo asegurar el Semestre.'];

        // 4) Ofertas
        $ids = [
            'templateId' => (string)$tplId,
            'semesterId' => (string)$semId,
            'offerings'  => [],
            'usuarios'   => [],
            'inscritos'  => [],
        ];

        foreach ($prev['ofertas'] as $of) {
            $offId = $this->bs->ensureOffering($of['codigo'], $of['nombre'], (int)$tplId, (int)$semId);
            if ($offId) $ids['offerings'][$of['codigo']] = (string)$offId;
        }

        // 5) Usuarios
        foreach ($prev['usuarios'] as $u) {
            $rolNombre = mb_strtolower($u['rol'] ?? 'estudiante');
            $rolId     = $roles[$rolNombre] ?? ($rolNombre === 'docente' ? 109 : 110);

            $userId = $this->bs->findOrCreateUser([
                'org_defined_id' => $u['idAcademusoft'],
                'nombres'        => $u['nombres'],
                'apellidos'      => $u['apellidos'],
                'usuario'        => $u['usuario'],
                'email'          => $u['email'],
                'rol_id'         => $rolId,
                'activo'         => $u['activo'] ?? true,
            ]);

            if ($userId) {
                $ids['usuarios'][$u['idAcademusoft']] = (int)$userId;
            }
        }

        // 6) Inscripciones
        foreach ($prev['inscripciones'] as $row) {
            $codigoOferta = $row['codigoOferta'];
            $idUserAcad   = $row['idAcademusoftUsuario'];

            $ofId = isset($ids['offerings'][$codigoOferta]) ? (int)$ids['offerings'][$codigoOferta] : null;
            $uId  = isset($ids['usuarios'][$idUserAcad])    ? (int)$ids['usuarios'][$idUserAcad]    : null;

            $rolNombre = mb_strtolower($row['rol'] ?? 'estudiante');
            $rolId     = $roles[$rolNombre] ?? ($rolNombre === 'docente' ? 109 : 110);

            $ok = ($ofId && $uId) ? $this->bs->enroll($ofId, $uId, (int)$rolId) : false;

            $ids['inscritos'][] = [
                'oferta' => $codigoOferta,
                'user'   => $idUserAcad,
                'rol'    => $rolNombre,
                'ok'     => $ok
            ];
        }

        return ['ok'=>true, 'ids'=>$ids];
    }
}
