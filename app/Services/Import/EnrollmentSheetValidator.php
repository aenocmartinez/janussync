<?php

namespace App\Services\Import;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class EnrollmentSheetValidator
{
    public const SHEET_NAME = '6 inscripciones';
    public const REQUIRED_HEADERS = ['type','action','child_code','role_name','parent_code'];

    /** Lee la primera fila y valida cabeceras EXACTAS (en orden). */
    public static function validateHeaders(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws): array
    {
        $headers = [];
        $row1 = $ws->rangeToArray('A1:E1', null, true, true, true)[1]; // A1..E1
        foreach ($row1 as $v) { $headers[] = is_null($v) ? '' : trim((string)$v); }

        $ok = ($headers === self::REQUIRED_HEADERS);

        return [
            'ok'      => $ok,
            'found'   => $headers,
            'expected'=> self::REQUIRED_HEADERS,
        ];
    }

    /** Devuelve hasta N filas normalizadas (trim) para logging/diagnóstico. */
    public static function sampleRows(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws, int $limit = 10): array
    {
        $rows = [];
        $highest = $ws->getHighestRow();
        $end = min($highest, 1 + $limit);

        for ($r = 2; $r <= $end; $r++) {
            $row = $ws->rangeToArray("A{$r}:E{$r}", null, true, true, true)[$r];
            $rows[] = [
                'type'        => trim((string)($row['A'] ?? '')),
                'action'      => trim((string)($row['B'] ?? '')),
                'child_code'  => trim((string)($row['C'] ?? '')),
                'role_name'   => trim((string)($row['D'] ?? '')),
                'parent_code' => trim((string)($row['E'] ?? '')),
                'row'         => $r,
            ];
        }
        return $rows;
    }
}
