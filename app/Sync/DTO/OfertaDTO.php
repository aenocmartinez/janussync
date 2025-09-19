<?php

namespace App\Sync\DTO;

final class OfertaDTO
{
    public function __construct(
        public readonly string $codigo,          // ej: MAT101-2025-2
        public readonly string $nombre,          // ej: Matemáticas I (2025-2)
        public readonly string $codigoPlantilla, // referencia a PlantillaDTO::codigo
        public readonly string $codigoSemestre   // referencia a SemestreDTO::codigo
    ) {}
}
