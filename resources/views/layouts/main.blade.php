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
            <a href="{{ url('/dashboard') }}">
                <img src="{{ asset('logo_principal.png') }}" alt="Logo Universidad" class="h-8">
            </a>
        </div>
        <div class="flex items-center relative cursor-pointer hidden lg:flex" id="user-menu-button">
            <div class="avatar-inicial">
                {{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
            </div>
            <span id="user-name" class="ml-2 mr-2">{{ Auth::user()->name }}</span>
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
                <li>
                    <a href="{{ url('/users') }}" class="block p-2 rounded {{ request()->is('users*') ? 'bg-blue-700' : 'hover:bg-blue-700' }}">
                        Usuarios
                    </a>
                </li>
                <li>
                    <a href="{{ url('/roles') }}" class="block p-2 rounded {{ request()->is('roles*') ? 'bg-blue-700' : 'hover:bg-blue-700' }}">
                        Roles
                    </a>
                </li>                                  
                <li>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();" class="block p-2 rounded hover:bg-blue-700">
                        Cerrar sesión
                    </a>
                </li>
                <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
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
                <li>
                    <a href="{{ url('/users') }}" class="block p-2 rounded {{ request()->is('users*') ? 'bg-gray-300 text-black' : 'text-gray-700 hover:bg-gray-300' }}">
                        Usuarios
                    </a>
                </li> 
                <li>
                    <a href="{{ url('/roles') }}" class="block p-2 rounded {{ request()->is('roles*') ? 'bg-gray-300 text-black' : 'text-gray-700 hover:bg-gray-300' }}">
                        Roles
                    </a>
                </li>                                
                <!-- Más enlaces de navegación aquí -->
            </ul>
        </nav>

        <div class="flex flex-col flex-1">
            <main class="flex-1 p-6 bg-white overflow-auto">

                <!-- Mensaje de éxito -->
                @if(session('success'))
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg shadow-sm mb-6 relative" role="alert">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-emerald-500 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M16.707 4.293a1 1 0 010 1.414L8.414 14l-4.707-4.707a1 1 0 011.414-1.414l3.293 3.293 7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <strong class="font-semibold">¡Éxito!</strong>
                        </div>
                        <span class="block sm:inline mt-1">{{ session('success') }}</span>
                        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-emerald-500 focus:outline-none" aria-label="Cerrar" onclick="this.parentElement.classList.add('hidden')">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M10 8.293l3.293-3.293a1 1 0 011.414 1.414L11.414 10l3.293 3.293a1 1 0 01-1.414 1.414L10 11.414l-3.293 3.293a1 1 0 01-1.414-1.414L8.586 10 5.293 6.707a1 1 0 011.414-1.414L10 8.293z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg shadow-sm mb-6 relative" role="alert">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M8.257 3.099c.366-.446 1.077-.446 1.443 0l6.482 7.94A1 1 0 0115.482 12H4.518a1 1 0 01-.76-1.61l6.499-7.947zM10 15a1 1 0 112 0 1 1 0 01-2 0z" clip-rule="evenodd" />
                            </svg>
                            <strong class="font-semibold">Advertencia:</strong>
                        </div>
                        <span class="block sm:inline mt-1">{{ session('warning') }}</span>
                        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-yellow-500 focus:outline-none" aria-label="Cerrar" onclick="this.parentElement.classList.add('hidden')">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M10 8.293l3.293-3.293a1 1 0 011.414 1.414L11.414 10l3.293 3.293a1 1 0 01-1.414 1.414L10 11.414l-3.293 3.293a1 1 0 01-1.414-1.414L8.586 10 5.293 6.707a1 1 0 011.414-1.414L10 8.293z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg shadow-sm mb-6 relative" role="alert">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v4a1 1 0 002 0V7zm-1 7a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
                            </svg>
                            <strong class="font-semibold">Error:</strong>
                        </div>
                        <span class="block sm:inline mt-1">{{ session('error') }}</span>
                        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-red-500 focus:outline-none" aria-label="Cerrar" onclick="this.parentElement.classList.add('hidden')">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M10 8.293l3.293-3.293a1 1 0 011.414 1.414L11.414 10l3.293 3.293a1 1 0 01-1.414 1.414L10 11.414l-3.293 3.293a1 1 0 01-1.414-1.414L8.586 10 5.293 6.707a1 1 0 011.414-1.414L10 8.293z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                @endif

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
    <script src="{{ asset('js/confirm-delete.js') }}"></script>
</body>
</html>
