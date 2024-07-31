<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Universidad Colegio Mayor de Cundinamarca</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>
<body>
    <div class="login-container">
        <div class="left-section">
            <img src="{{ asset('logo_principal.png') }}" alt="Universidad Colegio Mayor de Cundinamarca" class="university-logo">
            <div class="university-name">Universidad Colegio Mayor de Cundinamarca</div>
            <div class="university-department">Subdirección de educación virtual</div>
        </div>
        <div class="right-section">
            <h1 class="login-title">Iniciar sesión</h1>
            <form action="{{ route('login') }}" method="POST" class="login-form">
                @csrf
                <div class="input-group">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" required placeholder="Ingresa tu usuario">
                </div>
                <div class="input-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required placeholder="Ingresa tu contraseña">
                </div>
                <div id="captcha-container" class="input-group">
                    <label for="captcha">Captcha</label>
                    <input type="text" id="captcha" name="captcha" required placeholder="Ingresa el captcha">
                    <!-- Aquí deberías añadir tu implementación del captcha -->
                </div>
                <button type="submit" class="login-button">Ingresar</button>
                <a href="{{ route('password.request') }}" class="forgot-password">¿Olvidaste tu contraseña?</a>
            </form>
            <footer class="footer">
                <span>&copy; {{ date('Y') }} Universidad Colegio Mayor de Cundinamarca</span>
            </footer>
        </div>
    </div>
</body>
</html>
