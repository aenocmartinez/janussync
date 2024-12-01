<?php

namespace App\DataTransferObjects;

class UserDTO
{
    public $nombres;
    public $apellidos;
    public $email;
    public $usuario_id;
    public $usuario_codigo;
    public $usuario_tipo_documento;
    public $usuario_numero_documento;
    public $usuario_direccion;
    public $usuario_telefono;
    public $usuario_sexo;
    public $programa_nombre;
    public $programa_id;
    public $programa_modalidad;
    public $rol_usuario;

    public function __construct($nombres, 
                                $apellidos,
                                $usuario_tipo_documento,
                                $usuario_numero_documento,                                
                                $email,
                                $rol_usuario,
                                $usuario_id = "N/A",
                                $usuario_codigo = "N/A",
                                $usuario_direccion = "N/A",
                                $usuario_telefono = "N/A",
                                $usuario_sexo = "N/A",
                                $programa_nombre = "N/A",
                                $programa_id = "N/A",
                                $programa_modalidad = "N/A",                               
                                )
                                {
                                    $this->nombres = $nombres;
                                    $this->apellidos = $apellidos;
                                    $this->usuario_tipo_documento = $usuario_tipo_documento;
                                    $this->usuario_numero_documento = $usuario_numero_documento;
                                    $this->email = $email;
                                    $this->rol_usuario = $rol_usuario;
                                    $this->usuario_id = $usuario_id;
                                    $this->usuario_codigo = $usuario_codigo;
                                    $this->usuario_direccion = $usuario_direccion;
                                    $this->usuario_telefono = $usuario_telefono;
                                    $this->usuario_sexo = $usuario_sexo;
                                    $this->programa_nombre = $programa_nombre;
                                    $this->programa_id = $programa_id;
                                    $this->programa_modalidad = $programa_modalidad;
                                }
}
