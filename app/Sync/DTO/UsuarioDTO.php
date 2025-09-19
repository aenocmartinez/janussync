<?php

namespace App\Sync\DTO;

final class UsuarioDTO
{
    public function __construct(
        public readonly string $idAcademusoft, // OrgDefinedId en Brightspace
        public readonly string $nombres,
        public readonly string $apellidos,
        public readonly string $usuario,       // sugerido: nombre.apellido
        public readonly ?string $email = null,
        public readonly string $rol = 'ESTUDIANTE', // 'ESTUDIANTE' | 'DOCENTE'
        public readonly bool $activo = true
    ) {}
}
