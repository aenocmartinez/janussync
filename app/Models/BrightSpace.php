<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\DataTransferObjects\GradeDTO;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class BrightSpace extends Model
{
    use HasFactory;

    protected static $connectionName = 'mysql_brightspace';

    public static function validatedConnection() {
        try {
            DB::connection(self::$connectionName)->getPdo();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function getGrades($term = 1): Collection
    {
        try {
            $results = DB::connection(self::$connectionName)
                ->table('grades')
                ->select('id', 'user_id', 'course_id', 'grade', 'grade_type', 'term_number')
                ->where('term_number', '=', $term)
                ->get();

            return $results->map(function ($grade) {
                return GradeDTO::fromObject($grade);
            });

        } catch (Exception $e) {
            Log::info($e->getMessage());
            return collect();
        }
    }
}
