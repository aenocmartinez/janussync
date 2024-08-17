<div class="bg-white p-4 rounded-lg shadow-md flex flex-col justify-between box-border">
    <div class="flex items-center box-border">
        <i class="fas fa-server text-gray-600 text-2xl md:text-4xl mr-3"></i>
        <div>
            <h2 class="text-md md:text-lg font-semibold text-gray-700">{{ $title }}</h2>
            <p class="text-xs md:text-sm text-gray-500">Estado actual del servicio</p>
        </div>
    </div>
    <div class="flex items-center mt-2">
        @isset($connectionStatus)
            <span id="{{ $id }}" class="{{ $connectionStatus ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} text-xs font-semibold px-2 py-0.5 rounded-full">
                {{ $connectionStatus ? 'Conectado' : 'Sin conexión' }}
            </span>
        @else
            <span class="text-gray-500 text-xs font-semibold px-2 py-0.5 rounded-full">Estado desconocido</span>
        @endisset
    </div>
</div>
