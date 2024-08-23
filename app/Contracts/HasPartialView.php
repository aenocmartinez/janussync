<?php

namespace App\Contracts;

interface HasPartialView
{
    public static function getPartialViewName(): string;
}
