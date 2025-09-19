<?php

namespace App\Sync\DTO;

final class SemestreDTO
{
    public function __construct(
        public readonly string $codigo,        // ej: 2025-2
        public readonly string $nombre,        // ej: Período 2025-2
        public readonly ?string $fechaInicio = null, // ISO 8601 o null
        public readonly ?string $fechaFin    = null  // ISO 8601 o null
    ) {}
}
