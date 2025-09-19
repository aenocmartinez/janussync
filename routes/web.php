<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ScheduledTaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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


require __DIR__.'/auth.php';
