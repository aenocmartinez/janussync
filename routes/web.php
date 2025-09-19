<?php


use App\Http\Controllers\SyncController;

use App\Models\BrightSpace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Sync\Providers\ExcelProveedor;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ScheduledTaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use App\Sync\Brightspace\BrightspaceClient;
use App\Sync\Aplicar\AplicadorExcelService;

Route::get('/', function () {
    // return view('welcome');
    return view('auth.custom.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');    
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');   
    
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');    
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');  

    Route::resource('scheduled-tasks', ScheduledTaskController::class);
    Route::get('/monitoring/detail/{task_id}/{action}', [MonitoringController::class, 'showDetail'])->name('monitoring.detail');

});

Route::get('/check-connection-status', [MonitoringController::class, 'checkConnectionStatus'])->name('check.connection.status');
Route::get('/monitoring/run-tasks', [MonitoringController::class, 'executeScheduledTasks'])->name('monitoring.run-tasks');
Route::get('/monitoring/run-task/{id}', [MonitoringController::class, 'runTaskById'])->name('monitoring.run-task');


Route::get('/brightspace/keytool/start', function () {
    $appId  = env('BRIGHTSPACE_APP_ID');
    $appKey = env('BRIGHTSPACE_APP_KEY');

    $target = 'https://janussync.test/brightspace/keytool/callback';

    $rawSig = hash_hmac('sha256', $target, $appKey, true);
    $x_b = rtrim(strtr(base64_encode($rawSig), '+/', '-_'), '=');

    $qs = http_build_query([
        'x_a'      => $appId,
        'x_b'      => $x_b,
        'x_target' => $target,
    ]);

    return redirect()->away("https://unicolmayortest.brightspace.com/d2l/auth/api/token?{$qs}");
});


// 👇 Callback de Brightspace (ID Key)
Route::get('/brightspace/keytool/callback', function (Request $request) {
    // Brightspace retorna: x_a=tokenID, x_b=tokenKey, x_c=tokenSig
    $tokenId  = $request->query('x_a'); // será BRIGHTSPACE_USER_ID
    $tokenKey = $request->query('x_b'); // será BRIGHTSPACE_USER_KEY
    $tokenSig = $request->query('x_c'); // firma de (tokenId & tokenKey) con App Key

    $appKey = env('BRIGHTSPACE_APP_KEY', '');

    if (!$tokenId || !$tokenKey) {
        $recibido = $request->query();
        $html = '<h1>Callback Brightspace</h1><p>Faltan parámetros mínimos (x_a, x_b).</p>';
        $html .= '<h3>Recibido:</h3><pre style="background:#111;color:#eee;padding:12px;border-radius:8px;">'
              . htmlentities(print_r($recibido, true))
              . '</pre><p><a href="/brightspace/keytool/start">Intentar de nuevo</a></p>';
        return response($html, 400)->header('Content-Type', 'text/html; charset=utf-8');
    }

    // Verificación opcional de la firma x_c (tokenSig) con tu App Key
    $expectedRaw = hash_hmac('sha256', $tokenId . '&' . $tokenKey, $appKey, true);
    $expectedSig = rtrim(strtr(base64_encode($expectedRaw), '+/', '-_'), '=');
    $firmaOk = $tokenSig && hash_equals($expectedSig, $tokenSig);

    $env = <<<ENV
BRIGHTSPACE_HOST=unicolmayortest.brightspace.com
BRIGHTSPACE_PORT=443
BRIGHTSPACE_SCHEME=https

BRIGHTSPACE_APP_ID={env('BRIGHTSPACE_APP_ID')}
BRIGHTSPACE_APP_KEY={$appKey}

BRIGHTSPACE_USER_ID={$tokenId}
BRIGHTSPACE_USER_KEY={$tokenKey}
BRIGHTSPACE_DEFAULT_ROLE_ID=110
ENV;

    $html = '<h1>Llaves Brightspace (TEST)</h1>'
          . '<p>Copia este bloque en tu <code>.env</code>:</p>'
          . '<pre style="background:#111;color:#eee;padding:12px;border-radius:8px;">'
          . htmlentities($env)
          . '</pre>';
    if ($tokenSig) {
        $html .= '<p>Firma recibida <code>x_c</code>: <code>' . htmlentities($tokenSig) . '</code><br>'
              . 'Verificación local con tu App Key: <strong>' . ($firmaOk ? 'OK ✔️' : 'NO COINCIDE ⚠️') . '</strong></p>';
    }
    $html .= '<p><a href="/brightspace/keytool/start">Volver a intentar</a></p>';

    return response($html, 200)->header('Content-Type', 'text/html; charset=utf-8');
});


Route::get('/brightspace/health', function () {
    $host   = env('BRIGHTSPACE_HOST', 'unicolmayortest.brightspace.com');
    $scheme = env('BRIGHTSPACE_SCHEME', 'https');
    $port   = (int) env('BRIGHTSPACE_PORT', 443);

    $appId   = env('BRIGHTSPACE_APP_ID');
    $appKey  = env('BRIGHTSPACE_APP_KEY');
    $userId  = env('BRIGHTSPACE_USER_ID');
    $userKey = env('BRIGHTSPACE_USER_KEY');

    $factoryClass = '\\D2LAppContextFactory';
    if (!class_exists($factoryClass)) {
        return response()->json(['ok' => false, 'error' => 'No se encontró D2LAppContextFactory'], 500);
    }

    // ---- Crear AppContext (tu build: método NO estático) ----
    try {
        $factory = new \D2LAppContextFactory();
        if (!method_exists($factory, 'createSecurityContext')) {
            return response()->json(['ok' => false, 'error' => 'La fábrica no tiene createSecurityContext'], 500);
        }
        $appCtx = $factory->createSecurityContext($appId, $appKey);
    } catch (\Throwable $e) {
        return response()->json(['ok' => false, 'error' => 'No se pudo crear AppContext: '.$e->getMessage()], 500);
    }

    // ---- Intentar variantes para crear UserContext y validar host ----
    $variants = [];
    if (method_exists($appCtx, 'createUserContextWithValues')) {
        $variants[] = ['createUserContextWithValues(uid,key,scheme,host,port)', function() use ($appCtx,$userId,$userKey,$scheme,$host,$port) {
            return $appCtx->createUserContextWithValues($userId, $userKey, $scheme, $host, $port);
        }];
    }
    if (method_exists($appCtx, 'createUserContext')) {
        $variants[] = ['createUserContext(host,port,scheme,uid,key)', function() use ($appCtx,$host,$port,$scheme,$userId,$userKey) {
            return $appCtx->createUserContext($host, $port, $scheme, $userId, $userKey);
        }];
        $variants[] = ['createUserContext(uid,key,scheme,host,port)', function() use ($appCtx,$userId,$userKey,$scheme,$host,$port) {
            return $appCtx->createUserContext($userId, $userKey, $scheme, $host, $port);
        }];
        $variants[] = ['createUserContext(uid,key,host,port,scheme)', function() use ($appCtx,$userId,$userKey,$host,$port,$scheme) {
            return $appCtx->createUserContext($userId, $userKey, $host, $port, $scheme);
        }];
        $variants[] = ['createUserContext(scheme,host,port,uid,key)', function() use ($appCtx,$scheme,$host,$port,$userId,$userKey) {
            return $appCtx->createUserContext($scheme, $host, $port, $userId, $userKey);
        }];
    }
    if (method_exists($appCtx, 'createUserContextFromHost')) {
        $variants[] = ['createUserContextFromHost(host,port,scheme,uid,key)', function() use ($appCtx,$host,$port,$scheme,$userId,$userKey) {
            return $appCtx->createUserContextFromHost($host, $port, $scheme, $userId, $userKey);
        }];
    }

    $tried = [];
    $signed = null;
    $picked = null;
    $userCtx = null;

    foreach ($variants as [$label, $make]) {
        try {
            $ctx = $make();
            // firmar URL y validar que el host sea correcto
            $tmpSigned = method_exists($ctx, 'createAuthenticatedUrl')
                ? (string) $ctx->createAuthenticatedUrl('/d2l/api/lp/1.0/users/whoami', 'GET')
                : (string) $ctx->createAuthenticatedUri('/d2l/api/lp/1.0/users/whoami', 'GET');

            $parsed = parse_url($tmpSigned);
            $tried[] = ['label' => $label, 'signedHost' => $parsed['host'] ?? null, 'signed' => $tmpSigned];

            if (!empty($parsed['host']) && $parsed['host'] === $host) {
                $userCtx = $ctx;
                $signed  = $tmpSigned;
                $picked  = $label;
                break;
            }
        } catch (\Throwable $e) {
            $tried[] = ['label' => $label, 'error' => $e->getMessage()];
        }
    }

    if (!$userCtx) {
        return response()->json([
            'ok' => false,
            'error' => 'No se pudo construir UserContext con host válido.',
            'hostEsperado' => $host,
            'intentos' => $tried,
        ], 500);
    }

    // ---- Llamar whoami ----
    $resp = Http::timeout(12)->acceptJson()->get($signed);
    if ($resp->ok()) {
        return response()->json([
            'ok' => true,
            'picked' => $picked,
            'whoami' => $resp->json(),
        ]);
    }
    return response()->json([
        'ok' => false,
        'picked' => $picked,
        'status' => $resp->status(),
        'body' => $resp->body(),
        'signed' => $signed,
    ], $resp->status());
});

Route::get('/brightspace/test-create-user', function () {
    // Genera datos únicos para evitar choques por duplicados
    $ts    = date('Ymd_His');
    $fname = 'AbiTest';
    $lname = 'Uno'.$ts;

    $user = [
        'first_name' => $fname,
        'last_name'  => $lname,
        'email'      => null,           // déjalo null si no quieres correo real
        'role'       => 'ESTUDIANTE',   // o 'DOCENTE' para roleId 109 (según tu lógica)
    ];

    $ok = BrightSpace::createUsers([$user]);
    return $ok ? "OK: creado intento para {$fname}.{$lname}" : response("FALLÓ (revisa laravel.log)", 500);
});

Route::get('/brightspace/test-find-user', function (Request $request) {
    $u = $request->query('u'); // ej: abitest.uno20250919_035059
    if (!$u) {
        return response('Falta el parámetro ?u=nombre_de_usuario', 400);
    }

    $config = config('brightspace');
    $libPath = $config['libpath'];

    require_once $libPath . '/D2LAppContextFactory.php';
    require_once $libPath . '/D2LHostSpec.php';

    try {
        // App/User context (misma variante que ya funcionó contigo)
        $factory   = new \D2LAppContextFactory();
        $appCtx    = $factory->createSecurityContext($config['app_id'], $config['app_key']);
        $hostSpec  = new \D2LHostSpec($config['host'], (int)$config['port'], $config['scheme']);
        $userCtx   = $appCtx->createUserContextFromHostSpec($hostSpec, $config['user_id'], $config['user_key']);

        // Firmar GET a /users/ con query ?userName=...
        $path = '/d2l/api/lp/1.50/users/?' . http_build_query(['userName' => $u]);
        $uri  = $userCtx->createAuthenticatedUri($path, 'GET');

        // cURL simple
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200) {
            return response()->json(['ok' => false, 'status' => $code, 'body' => $body], $code);
        }

        $json = json_decode($body, true);
        return response()->json(['ok' => true, 'results' => $json], 200);

    } catch (\Throwable $e) {
        return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
    }
});

Route::get('/sync/preview', function (Request $req) {
    // Por defecto: storage/app/imports/CARGUE MASIVO BRIGHTSPACE.xlsx
    $archivo = $req->query('file', storage_path('app/imports/CARGUE MASIVO BRIGHTSPACE.xlsx'));
    if (!file_exists($archivo)) {
        return response()->json(['ok'=>false, 'error'=>"No existe el archivo en: {$archivo}"], 404);
    }
    try {
        $prov = new ExcelProveedor($archivo);
        $plantillas     = $prov->plantillas();
        $semestres      = $prov->semestres();
        $ofertas        = $prov->ofertas();
        $usuarios       = $prov->usuarios();
        $inscripciones  = $prov->inscripciones();

        return response()->json([
            'ok' => true,
            'archivo' => $archivo,
            'conteo' => [
                'plantillas'    => $plantillas->count(),
                'semestres'     => $semestres->count(),
                'ofertas'       => $ofertas->count(),
                'usuarios'      => $usuarios->count(),
                'inscripciones' => $inscripciones->count(),
            ],
            'muestra' => [
                'plantillas'    => array_slice($plantillas->all(), 0, 3),
                'semestres'     => array_slice($semestres->all(), 0, 3),
                'ofertas'       => array_slice($ofertas->all(), 0, 3),
                'usuarios'      => array_slice($usuarios->all(), 0, 3),
                'inscripciones' => array_slice($inscripciones->all(), 0, 3),
            ],
        ]);
    } catch (\Throwable $e) {
        return response()->json(['ok'=>false, 'error'=>$e->getMessage()], 500);
    }
});


Route::get('/sync/inspeccion-hojas', function (Request $req) {
    $archivo = $req->query('file', storage_path('app/imports/CARGUE MASIVO BRIGHTSPACE.xlsx'));
    if (!file_exists($archivo)) {
        return response()->json(['ok'=>false, 'error'=>"No existe el archivo en: {$archivo}"], 404);
    }

    // Helpers locales (mismos criterios que el proveedor)
    $normaliza = function ($s) {
        if ($s === null) return '';
        $s = (string)$s;
        $s = trim(mb_strtolower($s));
        $s = strtr($s, [
            'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
            'ä'=>'a','ë'=>'e','ï'=>'i','ö'=>'o','ü'=>'u','ñ'=>'n'
        ]);
        $s = preg_replace('/[^a-z0-9]+/u', '_', $s);
        $s = preg_replace('/_+/', '_', $s);
        return trim($s, '_');
    };
    $normalizaNombreHoja = function ($s) use ($normaliza) {
        $s = $normaliza($s);
        $s = preg_replace('/^(hoja|sheet)_*/', '', $s);
        $s = preg_replace('/-+/', '_', $s);
        return $s;
    };

    // Cargar Excel
    $ss = IOFactory::load($archivo);
    $nombres = $ss->getSheetNames();

    $resultado = [
        'ok'      => true,
        'archivo' => $archivo,
        'tamanio' => filesize($archivo),
        'hojas'   => [],
    ];

    foreach ($nombres as $idx => $nombreCrudo) {
        $sheet = $ss->getSheet($idx);
        $rows  = $sheet->toArray(null, true, true, true);

        // Detectar fila de encabezados:
        $headerRowIndex = null;
        $headers = [];
        if (!empty($rows)) {
            foreach ($rows as $i => $row) {
                // ¿Tiene al menos 2 celdas no vacías?
                $nnz = 0;
                foreach ($row as $cell) {
                    if ($cell !== null && trim((string)$cell) !== '') { $nnz++; }
                }
                if ($nnz >= 2) {
                    // Construir headers normalizados (vacíos -> col_N)
                    $colNum = 0;
                    foreach (array_values($row) as $cell) {
                        $k = $normaliza(($cell === null) ? '' : (string)$cell);
                        if ($k === '') $k = 'col_'.(++$colNum);
                        $headers[] = $k;
                    }
                    $headerRowIndex = $i;
                    break;
                }
            }
        }

        // Tomar 3 filas después de los headers como muestra
        $muestra = [];
        if ($headerRowIndex !== null) {
            // ordenar por indice (por si viene 1,2,3...)
            $idxs = array_keys($rows);
            sort($idxs, SORT_NUMERIC);
            $pos  = array_search($headerRowIndex, $idxs, true);
            for ($p = $pos + 1; $p < min($pos + 4, count($idxs)); $p++) {
                $cells = array_values($rows[$idxs[$p]] ?? []);
                $assoc = [];
                for ($j = 0; $j < count($headers); $j++) {
                    $assoc[$headers[$j]] = isset($cells[$j]) ? trim((string)$cells[$j]) : '';
                }
                // solo si no está totalmente vacía
                if (implode('', array_values($assoc)) !== '') {
                    $muestra[] = $assoc;
                }
            }
        }

        $resultado['hojas'][] = [
            'index'            => $idx,
            'nombre_crudo'     => $nombreCrudo,
            'nombre_normalizado' => $normalizaNombreHoja($nombreCrudo),
            'total_filas'      => count($rows),
            'fila_headers'     => $headerRowIndex,
            'headers_normalizados' => $headers,
            'muestra'          => $muestra,
        ];
    }

    return response()->json($resultado);
});



// Route::get('/sync/aplicar-prueba', function (Request $request) {
//     $archivo = Storage::path('imports/CARGUE MASIVO BRIGHTSPACE.xlsx');
//     if (!file_exists($archivo)) {
//         return response()->json([
//             'ok' => false,
//             'error' => "No existe el archivo en: {$archivo}. Sube el Excel y vuelve a intentar."
//         ], 404);
//     }

//     $service = new AplicadorExcelService(
//         new ExcelProveedor($archivo),
//         new BrightspaceClient()
//     );

//     $res = $service->aplicar();
//     $http = ($res['ok'] ?? false) ? 200 : 422;
//     return response()->json($res, $http);
// });

Route::get('/sync/aplicar-prueba', function (Request $request) {
    // Rutas absoluta y relativa (ambas)
    $rel = 'imports/CARGUE MASIVO BRIGHTSPACE.xlsx';
    $abs = Storage::path($rel);

    // Por defecto borra al final; desactiva con ?del=0
    $borrarAlFinal = $request->boolean('del', true);

    try {
        if (!file_exists($abs)) {
            return response()->json([
                'ok' => false,
                'error' => "No existe el archivo en: {$abs}. Sube el Excel y vuelve a intentar."
            ], 404);
        }

        $prov    = new ExcelProveedor($abs);
        $service = new AplicadorExcelService($prov, new BrightspaceClient());

        $res  = $service->aplicar();
        $http = ($res['ok'] ?? false) ? 200 : 422;

        return response()->json($res, $http);

    } finally {
        if ($borrarAlFinal) {
            // 1) Intentar liberar recursos
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
            usleep(100000); // 100 ms por si el handler tarda en cerrarse

            // 2) Intentar borrar por ambas rutas
            //    (algunas veces unlink del ABS falla, pero Storage sí lo borra)
            clearstatcache();

            $borroAbs = false;
            if (file_exists($abs)) {
                $borroAbs = @unlink($abs);
            }

            $borroRel = false;
            if (Storage::exists($rel)) {
                $borroRel = Storage::delete($rel);
            }

            // 3) Si no se pudo borrar, renombrar para no bloquear procesos siguientes
            if ((file_exists($abs) || Storage::exists($rel)) && (!$borroAbs && !$borroRel)) {
                $nuevoRel = 'imports/CARGUE MASIVO BRIGHTSPACE.xlsx.old-' . now()->format('Ymd_His');
                // Si existe por Storage
                if (Storage::exists($rel)) {
                    @Storage::move($rel, $nuevoRel);
                } elseif (file_exists($abs)) {
                    @rename($abs, dirname($abs) . '/CARGUE MASIVO BRIGHTSPACE.xlsx.old-' . time());
                }
            }
        }
    }
});


Route::get('/debug/roles', function () {
    $cfg = config('brightspace');
    $host   = $cfg['host'];    $port = $cfg['port'];   $scheme = $cfg['scheme'];
    $appId  = $cfg['app_id'];  $appKey = $cfg['app_key'];
    $uid    = $cfg['user_id']; $ukey   = $cfg['user_key'];
    $lib    = $cfg['libpath'];

    $v_roles = '1.46';

    require_once $lib.'/D2LAppContextFactory.php';
    require_once $lib.'/D2LHostSpec.php';

    $ctx = (new \D2LAppContextFactory())
        ->createSecurityContext($appId, $appKey)
        ->createUserContextFromHostSpec(new \D2LHostSpec($host, $port, $scheme), $uid, $ukey);

    $req = function (string $method, string $path, $body = null) use ($ctx) {
        $uri = $ctx->createAuthenticatedUri($path, $method);
        $ch  = curl_init($uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if (!is_null($body)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [$code, $resp];
    };
    $json = fn($s) => $s ? json_decode($s, true) : null;

    [$c, $r] = $req('GET', "/d2l/api/lp/{$v_roles}/roles/");
    return response()->json(['http' => $c, 'data' => $json($r)]);
});


Route::get('/brightspace/outypes', function () {
    $c = config('brightspace');

    require_once $c['libpath'].'/D2LAppContextFactory.php';
    require_once $c['libpath'].'/D2LHostSpec.php';

    $factory  = new \D2LAppContextFactory();
    $sec      = $factory->createSecurityContext($c['app_id'], $c['app_key']);
    $hostSpec = new \D2LHostSpec($c['host'], $c['port'], $c['scheme']);
    $userCtx  = $sec->createUserContextFromHostSpec($hostSpec, $c['user_id'], $c['user_key']);

    // La versión 1.0 funciona bien para esto
    $uri = $userCtx->createAuthenticatedUri('/d2l/api/lp/1.0/outypes/', 'GET');

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $uri,
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $raw = curl_exec($ch);
    $http= curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http !== 200) {
        return response()->json(['ok'=>false,'http'=>$http,'raw'=>$raw], 500);
    }

    $data = json_decode($raw, true) ?: [];
    // Te dejo solo Id y Name para que sea fácil de leer
    $simple = array_map(fn($x)=>[
        'Id'   => $x['Id']   ?? null,
        'Name' => $x['Name'] ?? ($x['Code'] ?? null),
    ], $data);

    return response()->json(['ok'=>true,'tipos'=>$simple], 200, [], JSON_UNESCAPED_UNICODE);
});

Route::get('/debug/brightspace-config', function () {
    return response()->json([
        'ok' => true,
        'semester_type_id'        => config('brightspace.semester_type_id'),
        'template_type_id'        => config('brightspace.course_template_type_id'),
        'offering_type_id'        => config('brightspace.course_offering_type_id'),
        'department_type_id'      => config('brightspace.department_type_id'),
    ]);
});


Route::get('/sync/preview-excel', [SyncController::class, 'preview']);
Route::post('/sync/aplicar-excel', [SyncController::class, 'aplicar']);


require __DIR__.'/auth.php';
