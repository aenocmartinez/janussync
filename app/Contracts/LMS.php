<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface LMS
{
    public static function validatedConnection(): bool;
    public static function createUsers($users = []) : bool;
    public static function getGrades($term = 1): Collection;
}
