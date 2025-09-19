<?php

return [
    // Aliases (lo que puede venir en Excel) => nombre canónico en Brightspace
    'aliases' => [
        'docente'            => 'docente',
        'profesor'           => 'docente',
        'instructor'         => 'docente',
        'teacher'            => 'docente',

        'estudiante'         => 'estudiante',
        'alumno'             => 'estudiante',
        'student'            => 'estudiante',
    ],

    // Fallbacks por si no se puede leer /roles desde la API (no recomendado, pero útil)
    'fallback_ids' => [
        'docente'    => env('BRIGHTSPACE_TEACHER_ROLE_ID', 109),
        'estudiante' => env('BRIGHTSPACE_STUDENT_ROLE_ID', 110),
    ],
];
