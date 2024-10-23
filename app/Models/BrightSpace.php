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

    // public static function validatedConnection() {
    //     try {
    //         DB::connection(self::$connectionName)->getPdo();
    //         return true;
    //     } catch (Exception $e) {
    //         return false;
    //     }
    // }

    public static function validatedConnection()
    {
        $config = config('brightspace');

        $host = $config['host'];
        $port = $config['port'];
        $scheme = $config['scheme'];
        $appKey = $config['app_key'];
        $appId = $config['app_id'];
        $userKey = $config['user_key'];
        $userId = $config['user_id'];
        $libPath = $config['libpath'];

        require_once $libPath . '/D2LAppContextFactory.php';
        require_once $libPath . '/D2LHostSpec.php';

        $authContextFactory = new \D2LAppContextFactory();
        $authContext = $authContextFactory->createSecurityContext($appId, $appKey);
        $hostSpec = new \D2LHostSpec($host, $port, $scheme);

        try {
            $userContext = $authContext->createUserContextFromHostSpec($hostSpec, $userId, $userKey);
            $uri = $userContext->createAuthenticatedUri("/d2l/api/lp/1.0/users/whoami", "GET");

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $uri);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode == 200) {
                return true; 
            } else {
                Log::error("Error al conectar con BrightSpace: HTTP Code " . $httpCode);
                return false;
            }
        } catch (Exception $e) {
            Log::error("Excepción al conectar con BrightSpace: " . $e->getMessage());
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
