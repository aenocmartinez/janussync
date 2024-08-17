<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrightSpace extends Model
{
    use HasFactory;

    public static function validatedConnection() {
        return true;
    }
}
