<?php

namespace App\DataTransferObjects;

class CourseDTO
{
    public $templateId;
    public $name;
    public $code;

    public function __construct(string $templateId, string $name, string $code)
    {
        $this->templateId = $templateId;
        $this->name = $name;
        $this->code = $code;
    }
}
