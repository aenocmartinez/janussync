<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Universidad Colegio Mayor de Cundinamarca</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    {!! NoCaptcha::renderJs() !!}
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
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" autofocus autocomplete="email" required placeholder="Ingresa tu correo electrónico" class="{{ $errors->has('email') ? 'border-red-500' : '' }}">
                    @if ($errors->has('email'))
                        <p class="text-red-500 text-sm mt-1">{{ $errors->first('email') }}</p>
                    @endif
                </div>
                <div class="input-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Ingresa tu contraseña" class="{{ $errors->has('password') ? 'border-red-500' : '' }}">
                    @if ($errors->has('password'))
                        <p class="text-red-500 text-sm mt-1">{{ $errors->first('password') }}</p>
                    @endif
                </div>
                <div id="captcha-container" class="input-group">
                    {!! NoCaptcha::display() !!}
                    @if ($errors->has('g-recaptcha-response'))
                        <p class="text-red-500 text-sm mt-1">{{ $errors->first('g-recaptcha-response') }}</p>
                    @endif
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
