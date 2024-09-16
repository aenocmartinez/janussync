<?php

namespace App\DataTransferObjects;

class UserDTO
{
    public $first_name;
    public $last_name;
    public $email;
    public $estudiante_id;
    public $estudiante_codigo;
    public $estudiante_tipo_documento;
    public $estudiante_numero_documento;
    public $estudiante_direccion;
    public $estudiante_telefono;
    public $estudiante_sexo;
    public $programa_nombre;
    public $programa_id;
    public $programa_modalidad;

    public function __construct($first_name, 
                                $last_name,
                                $email,
                                $estudiante_id,
                                $estudiante_codigo,
                                $estudiante_tipo_documento,
                                $estudiante_numero_documento,
                                $estudiante_direccion,
                                $estudiante_telefono,
                                $estudiante_sexo,
                                $programa_nombre,
                                $programa_id,
                                $programa_modalidad
                                )
                                {
                                    $this->first_name = $first_name;
                                    $this->last_name = $last_name;
                                    $this->email = $email;
                                    $this->estudiante_id = $estudiante_id;
                                    $this->estudiante_codigo = $estudiante_codigo;
                                    $this->estudiante_tipo_documento = $estudiante_tipo_documento;
                                    $this->estudiante_numero_documento = $estudiante_numero_documento;
                                    $this->estudiante_direccion = $estudiante_direccion;
                                    $this->estudiante_telefono = $estudiante_telefono;
                                    $this->estudiante_sexo = $estudiante_sexo;
                                    $this->programa_nombre = $programa_nombre;
                                    $this->programa_id = $programa_id;
                                    $this->programa_modalidad = $programa_modalidad;
                                }
}
