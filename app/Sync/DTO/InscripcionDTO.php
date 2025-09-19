<?php

namespace App\Sync\DTO;

final class InscripcionDTO
{
    public function __construct(
        public readonly string $idAcademusoftUsuario, // UsuarioDTO::idAcademusoft
        public readonly string $codigoOferta,         // OfertaDTO::codigo
        public readonly string $rol                   // 'ESTUDIANTE' | 'DOCENTE'
    ) {}
}
