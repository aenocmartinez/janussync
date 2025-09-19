<?php

namespace App\Sync\Contracts;

use Illuminate\Support\Collection;
use App\Sync\DTO\{PlantillaDTO, SemestreDTO, OfertaDTO, UsuarioDTO, InscripcionDTO};

/**
 * Proveedor de datos canónicos para la sincronización.
 * Implementaciones: AcademusoftProveedor (regular), ExcelProveedor (alterno).
 */
interface Proveedor
{
    /** @return Collection<PlantillaDTO> */
    public function plantillas(): Collection;

    /** @return Collection<SemestreDTO> */
    public function semestres(): Collection;

    /** @return Collection<OfertaDTO> */
    public function ofertas(): Collection; // cursos por período (course offerings)

    /** @return Collection<UsuarioDTO> */
    public function usuarios(): Collection;

    /** @return Collection<InscripcionDTO> */
    public function inscripciones(): Collection;
}
