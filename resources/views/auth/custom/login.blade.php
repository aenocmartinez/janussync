<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Universidad Colegio Mayor de Cundinamarca</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    {!! NoCaptcha::renderJs() !!}
    <style>
        .login-container {
            display: flex;
            height: 100vh;
            flex-direction: column; /* Columna por defecto */
            align-items: center; /* Centrar contenido horizontalmente */
        }

        .left-section {
            display: none; /* Ocultar por defecto */
        }

        .right-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            flex: 1; /* Ocupa todo el espacio disponible */
            padding: 2rem;
            background-color: white;
            width: 100vw; /* Ocupa todo el ancho de la ventana */
            box-sizing: border-box; /* Incluye el padding en el ancho total */
        }

        .university-logo {
            margin-bottom: 1rem;
            width: 100px; /* Tamaño del logo */
        }

        .login-form {
            width: 100%;
            max-width: 400px; /* Establece un ancho máximo en escritorio */
        }

        .input-group {
            width: 100%;
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 1.125rem; /* Tamaño de fuente mayor */
            font-weight: 600;
            text-align: center; /* Centrar texto */
        }

        input {
            width: 100%;
            padding: 1rem;
            font-size: 1.125rem; /* Tamaño de fuente mayor */
            border: 1px solid #ccc;
            border-radius: 0.375rem;
            box-sizing: border-box;
        }

        .g-recaptcha {
            transform: scale(1.0); /* Ajustar escala para móviles */
            transform-origin: 0 0; /* Asegura el origen de la escala */
            width: 100% !important; /* Forzar el ancho completo */
            margin-bottom: 1.5rem;
        }

        .login-button {
            width: 100%;
            padding: 1rem;
            font-size: 1.125rem; /* Tamaño de fuente mayor */
            text-align: center; /* Centrar texto */
            display: flex;
            justify-content: center; /* Centrar contenido dentro del botón */
        }

        .forgot-password {
            display: block;
            text-align: center;
            font-size: 1rem;
            margin-top: 1rem;
        }

        .footer {
            font-size: 0.875rem; /* Tamaño de fuente */
            margin-top: 2rem;
            text-align: center;
        }

        @media (min-width: 768px) {
            .login-container {
                flex-direction: row;
                align-items: stretch; /* Estirar en pantallas grandes */
            }

            .left-section {
                display: flex !important; /* Mostrar en pantallas grandes */
                width: 50%; /* Ocupa el 50% del ancho */
                background-color: #00349a;
                color: white;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 2rem;
            }

            .right-section {
                width: 50%; /* Ocupa el 50% del ancho */
                max-width: 100%; /* Asegura que no haya desbordamiento */
                padding: 2rem; /* Restaurar padding en pantallas grandes */
            }

            .md\:hidden {
                display: none !important; /* Ocultar logo en pantallas grandes */
            }

            label, input, .login-button, .forgot-password, .footer {
                text-align: left; /* Ajuste de alineación para pantallas grandes */
            }

            input, .login-button {
                width: 100%; /* Restaurar ancho en pantallas grandes */
            }

            .login-form {
                margin: 0 auto; /* Centrar el formulario */
            }

            .g-recaptcha {
                transform: scale(1); /* Tamaño normal en pantallas grandes */
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Sección izquierda (50%) - Oculta en móviles -->
        <div class="left-section">
            <img src="{{ asset('logo_principal.png') }}" alt="Universidad Colegio Mayor de Cundinamarca" class="university-logo w-36">
            <div class="university-name text-3xl font-semibold text-center">Universidad Colegio Mayor de Cundinamarca</div>
            <div class="university-department mt-2 text-xl text-center">Subdirección de educación virtual</div>
        </div>

        <!-- Sección derecha (50%) -->
        <div class="right-section">
            <!-- Logo en móvil -->
            <div class="md:hidden flex justify-center mb-4">
                <img src="{{ asset('logo_principal.png') }}" alt="Universidad Colegio Mayor de Cundinamarca" class="university-logo">
            </div>
            <h1 class="login-title text-3xl mb-8 font-bold text-blue-900 text-center">Iniciar sesión</h1>
            <form action="{{ route('login') }}" method="POST" class="login-form">
                @csrf
                <div class="input-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" autofocus autocomplete="off"  required placeholder="Ingresa tu correo electrónico" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent {{ $errors->has('email') ? 'border-red-500' : '' }}">
                    @if ($errors->has('email'))
                        <p class="text-red-500 text-sm mt-1">{{ $errors->first('email') }}</p>
                    @endif
                </div>
                <div class="input-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Ingresa tu contraseña" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent {{ $errors->has('password') ? 'border-red-500' : '' }}">
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
                <button type="submit" class="login-button bg-blue-600 text-white rounded-md font-semibold transition duration-300 hover:bg-blue-700">Ingresar</button>
                <!-- <a href="{{ route('password.request') }}" class="forgot-password text-blue-600 hover:text-blue-700">¿Olvidaste tu contraseña?</a> -->
            </form>
            <footer class="footer text-gray-600">
                &copy; {{ date('Y') }} Universidad Colegio Mayor de Cundinamarca. Todos los derechos reservados.
            </footer>
        </div>
    </div>
</body>
</html>
