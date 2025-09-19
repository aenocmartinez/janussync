<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Sync\Providers\ExcelProveedor;
use App\Sync\Brightspace\BrightspaceClient;
use App\Sync\Aplicar\AplicadorExcelService;

class SyncController extends Controller
{
    /** Vista previa del Excel normalizado (lo mismo que /sync/preview) */
    public function preview(Request $request)
    {
        $archivo = Storage::path('imports/CARGUE MASIVO BRIGHTSPACE.xlsx');
        if (!file_exists($archivo)) {
            return response()->json([
                'ok' => false,
                'error' => "No existe el archivo en: {$archivo}. Sube el Excel y vuelve a intentar."
            ], 404);
        }

        $prov = new ExcelProveedor($archivo);
        $prev = $prov->previsualizar();

        return response()->json([
            'ok' => true,
            'conteo' => [
                'plantillas' => count($prev['plantillas'] ?? []),
                'semestres'  => count($prev['semestres']  ?? []),
                'ofertas'    => count($prev['ofertas']    ?? []),
                'usuarios'   => count($prev['usuarios']   ?? []),
                'inscripciones' => count($prev['inscripciones'] ?? []),
            ],
            'muestra' => [
                'plantillas'    => array_slice($prev['plantillas'] ?? [], 0, 3),
                'semestres'     => array_slice($prev['semestres'] ?? [], 0, 3),
                'ofertas'       => array_slice($prev['ofertas'] ?? [], 0, 3),
                'usuarios'      => array_slice($prev['usuarios'] ?? [], 0, 3),
                'inscripciones' => array_slice($prev['inscripciones'] ?? [], 0, 3),
            ]
        ]);
    }

    /** Aplica el Excel con el AplicadorExcelService (lo mismo que /sync/aplicar-prueba) */
    public function aplicar(Request $request)
    {
        $archivo = Storage::path('imports/CARGUE MASIVO BRIGHTSPACE.xlsx');
        if (!file_exists($archivo)) {
            return response()->json([
                'ok' => false,
                'error' => "No existe el archivo en: {$archivo}. Sube el Excel y vuelve a intentar."
            ], 404);
        }

        $service = new AplicadorExcelService(
            new ExcelProveedor($archivo),
            new BrightspaceClient()
        );

        $res = $service->aplicar();
        $http = ($res['ok'] ?? false) ? 200 : 422;

        return response()->json($res, $http);
    }
}
