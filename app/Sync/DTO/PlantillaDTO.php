<?php

namespace App\Sync\DTO;

final class PlantillaDTO
{
    public function __construct(
        public readonly string $codigo, // ej: MAT101
        public readonly string $nombre  // ej: Matemáticas I
    ) {}
}
