<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Default Title')</title>
    @vite('resources/css/app.css')
    <!-- Otros enlaces de estilos o scripts -->
</head>
<body class="min-h-screen bg-gray-100">
    <header class="bg-blue-800 text-white p-4">
        <!-- Contenido del encabezado -->
    </header>
    <main class="w-full max-w-7xl mx-auto p-4">
        @yield('content')
    </main>
    <footer class="bg-gray-800 text-white text-center p-4">
        <!-- Contenido del pie de página -->
    </footer>
    @vite('resources/js/app.js')
</body>
</html>
