<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Página')</title>
    @vite('resources/css/app.css')
    @vite('resources/css/main.css')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js" crossorigin="anonymous"></script>
</head>
<body class="flex flex-col min-h-screen">
    <header class="flex items-center justify-between p-4 bg-blue-800 text-white">
        <div class="flex items-center">
            <a href="{{ url('/') }}">
                <img src="{{ asset('logo_principal.png') }}" alt="Logo Universidad" class="h-8">
            </a>
        </div>
        <div class="flex items-center relative cursor-pointer" id="user-menu-button">
            <div class="avatar-inicial">
                {{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
            </div>
            <span id="user-name" class="ml-2 mr-2 hidden lg:block">{{ Auth::user()->name }}</span>
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            <div id="dropdown-menu" class="absolute right-0 mt-12 bg-white rounded-md shadow-lg z-20 hidden">
                <a href="{{ url('/profile') }}" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">Perfil</a>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">Cerrar sesión</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
        <button id="menu-toggle" class="lg:hidden text-white focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
        </button>
    </header>

    <nav id="mobile-menu" class="lg:hidden fixed inset-0 bg-blue-800 bg-opacity-75 z-50 hidden">
        <div class="flex flex-col items-center justify-center h-full">
            <ul class="text-white text-lg space-y-6">
                <li>
                    <a href="{{ url('/dashboard') }}" class="block p-2 rounded {{ request()->is('dashboard*') ? 'bg-blue-700' : 'hover:bg-blue-700' }}">
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ url('/profile') }}" class="block p-2 rounded {{ request()->is('profile*') ? 'bg-blue-700' : 'hover:bg-blue-700' }}">
                        Perfil
                    </a>
                </li>
                <li>
                    <a href="{{ url('/monitoring') }}" class="block p-2 rounded {{ request()->is('monitoring*') ? 'bg-blue-700' : 'hover:bg-blue-700' }}">
                        Monitoreo de Tareas
                    </a>
                </li>
                <!-- Más enlaces de navegación aquí -->
            </ul>
        </div>
    </nav>

    <div class="flex flex-1">
        <nav id="side-nav" class="w-64 bg-gray-100 p-4 flex-shrink-0 hidden lg:block">
            <ul class="space-y-2">
                <li>
                    <a href="{{ url('/dashboard') }}" class="block p-2 rounded {{ request()->is('dashboard*') ? 'bg-gray-300 text-black' : 'text-gray-700 hover:bg-gray-300' }}">
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ url('/profile') }}" class="block p-2 rounded {{ request()->is('profile*') ? 'bg-gray-300 text-black' : 'text-gray-700 hover:bg-gray-300' }}">
                        Perfil
                    </a>
                </li>
                <li>
                    <a href="{{ url('/monitoring') }}" class="block p-2 rounded {{ request()->is('monitoring*') ? 'bg-gray-300 text-black' : 'text-gray-700 hover:bg-gray-300' }}">
                        Monitoreo de Tareas
                    </a>
                </li>
                <!-- Más enlaces de navegación aquí -->
            </ul>
        </nav>

        <div class="flex flex-col flex-1">
            <main class="flex-1 p-6 bg-white overflow-auto">
                @yield('content')
            </main>
            <footer class="text-center py-4">
                &copy; {{ date('Y') }} Universidad Colegio Mayor de Cundinamarca. Todos los derechos reservados.
            </footer>
        </div>
    </div>

    <script>
        document.getElementById('user-menu-button').addEventListener('click', function() {
            var dropdown = document.getElementById('dropdown-menu');
            var userName = document.getElementById('user-name');
            dropdown.classList.toggle('hidden');
            userName.classList.toggle('hidden');
            dropdown.style.width = userName.offsetWidth + 'px';
        });

        // Cerrar el dropdown al hacer clic fuera
        window.addEventListener('click', function(e) {
            var dropdown = document.getElementById('dropdown-menu');
            var userName = document.getElementById('user-name');
            if (!document.getElementById('user-menu-button').contains(e.target)) {
                dropdown.classList.add('hidden');
                userName.classList.add('hidden');
            }
        });

        // Menú hamburguesa toggle
        document.getElementById('menu-toggle').addEventListener('click', function() {
            var mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });

        // Cerrar el menú móvil al hacer clic fuera
        window.addEventListener('click', function(e) {
            var mobileMenu = document.getElementById('mobile-menu');
            var menuToggle = document.getElementById('menu-toggle');
            if (!menuToggle.contains(e.target) && !mobileMenu.contains(e.target)) {
                mobileMenu.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
