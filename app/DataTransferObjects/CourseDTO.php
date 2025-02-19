<?php

namespace App\DataTransferObjects;

class CourseDTO
{
    public $templateId;
    public $sede;
    public $facultad;
    public $metodologia;
    public $nivelEducativo;
    public $modalidad;
    public $tipoPeriodoID;
    public $ubicacionSemestralMateria;
    public $materia;
    public $periodoAcademico;

    public function __construct(
        $templateId,
        $sede,
        $facultad,
        $metodologia,
        $nivelEducativo,
        $modalidad,
        $tipoPeriodoID,
        $ubicacionSemestralMateria,
        $materia,
        $periodoAcademico
    ) {
        $this->templateId = $templateId;
        $this->sede = $sede;
        $this->facultad = $facultad;
        $this->metodologia = $metodologia;
        $this->nivelEducativo = $nivelEducativo;
        $this->modalidad = $modalidad;
        $this->tipoPeriodoID = $tipoPeriodoID;
        $this->ubicacionSemestralMateria = $ubicacionSemestralMateria;
        $this->materia = $materia;
        $this->periodoAcademico = $periodoAcademico;
    }
}
