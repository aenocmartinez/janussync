<?php

namespace App\DataTransferObjects;

class GradeDTO
{
    public $id;
    public $user_id;
    public $course_id;
    public $grade;
    public $grade_type;
    public $term_number;

    private function __construct(){}

    public static function fromArray(array $data): self
    {
        $instance = new self();
        $instance->id = $data['id'] ?? null;
        $instance->user_id = $data['user_id'] ?? null;
        $instance->course_id = $data['course_id'] ?? null;
        $instance->grade = $data['grade'] ?? null;
        $instance->grade_type = $data['grade_type'] ?? null;
        $instance->term_number = $data['term_number'] ?? null;

        return $instance;
    }

    public static function fromObject($data): self
    {
        return self::fromArray((array) $data);
    }
}
