<?php

namespace App\Contracts;

interface HasModel
{
    public static function getModelClass(): string;
}
