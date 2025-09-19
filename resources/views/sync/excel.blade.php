@extends('layouts.main')

@section('title', 'Sincronización por Excel')

@section('content')
<div class="max-w-6xl mx-auto">
  <h1 class="text-2xl font-semibold mb-6">Sincronización por Excel</h1>

  {{-- Bloque 1: Cargar archivo --}}
  <div class="bg-white border rounded-lg p-5 mb-6">
    <h2 class="text-lg font-medium mb-4">1) Cargar archivo</h2>
    <form id="form-sync-upload"
      action="{{ route('sync.excel.upload') }}" method="POST"
      enctype="multipart/form-data"
      class="space-y-3"
      data-loading>

      @csrf
      <input type="file" name="archivo" accept=".xlsx,.xls"
             class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
             required>
      @error('archivo')
        <p class="text-red-600 text-sm">{{ $message }}</p>
      @enderror
      <button class="inline-flex items-center px-4 py-2 bg-blue-700 text-white rounded hover:bg-blue-800">
        Subir / Reemplazar
      </button>
      <p class="text-xs text-gray-500">Se guarda como: <code>storage/app/imports/{{ $archivo }}</code></p>
    </form>
  </div>

  {{-- Bloque 2: Previsualización --}}
  <div class="bg-white border rounded-lg p-5 mb-6">
    <h2 class="text-lg font-medium mb-4">2) Previsualización</h2>

    @if (!$existe)
      <p class="text-gray-500">Aún no hay archivo cargado.</p>
    @else
      @if (isset($preview['ok']) && $preview['ok'] === false)
        <p class="text-yellow-700 bg-yellow-50 border border-yellow-200 rounded px-3 py-2">
          {{ $preview['error'] }}
        </p>
      @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="p-3 bg-gray-50 rounded border">
            <div class="text-sm text-gray-500">Plantillas</div>
            <div class="text-2xl font-semibold">{{ count($preview['plantillas'] ?? []) }}</div>
          </div>
          <div class="p-3 bg-gray-50 rounded border">
            <div class="text-sm text-gray-500">Semestres</div>
            <div class="text-2xl font-semibold">{{ count($preview['semestres'] ?? []) }}</div>
          </div>
          <div class="p-3 bg-gray-50 rounded border">
            <div class="text-sm text-gray-500">Ofertas</div>
            <div class="text-2xl font-semibold">{{ count($preview['ofertas'] ?? []) }}</div>
          </div>
          <div class="p-3 bg-gray-50 rounded border">
            <div class="text-sm text-gray-500">Usuarios</div>
            <div class="text-2xl font-semibold">{{ count($preview['usuarios'] ?? []) }}</div>
          </div>
          <div class="p-3 bg-gray-50 rounded border">
            <div class="text-sm text-gray-500">Inscripciones</div>
            <div class="text-2xl font-semibold">{{ count($preview['inscripciones'] ?? []) }}</div>
          </div>
        </div>

        <details class="mt-4">
          <summary class="cursor-pointer text-blue-700 hover:underline">Ver muestra</summary>
          <pre class="mt-2 p-3 bg-gray-900 text-gray-100 rounded overflow-auto text-xs">
{{ json_encode([
  'plantillas'=> array_slice($preview['plantillas'] ?? [], 0, 3),
  'semestres' => array_slice($preview['semestres']  ?? [], 0, 3),
  'ofertas'   => array_slice($preview['ofertas']    ?? [], 0, 3),
  'usuarios'  => array_slice($preview['usuarios']   ?? [], 0, 3),
  'inscripciones'=> array_slice($preview['inscripciones'] ?? [], 0, 3),
], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}
          </pre>
        </details>
      @endif
    @endif
  </div>

  {{-- Bloque 3: Aplicar --}}
  <div class="bg-white border rounded-lg p-5">
    <h2 class="text-lg font-medium mb-4">3) Aplicar a Brightspace</h2>

    <form id="form-sync-apply"
        method="POST" action="{{ route('sync.excel.apply') }}"
        onsubmit="return confirm('¿Deseas aplicar estos cambios en Brightspace?');"
        class="space-y-3"
        data-loading>
        
      @csrf
      <label class="inline-flex items-center">
        <input type="checkbox" name="confirmar" class="mr-2 rounded border-gray-300" required>
        <span>Confirmo que deseo crear/actualizar plantilla, semestre, cursos, usuarios e inscripciones.</span>
      </label>
      @error('confirmar')
        <p class="text-red-600 text-sm">{{ $message }}</p>
      @enderror

      <button class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700"
              {{ $existe ? '' : 'disabled' }}>
        Aplicar ahora
      </button>
    </form>

    @if (session('resultado_sync'))
      <hr class="my-4">
      <h3 class="text-base font-medium mb-2">Resultado</h3>
      <pre class="p-3 bg-gray-900 text-gray-100 rounded overflow-auto text-xs">
{{ json_encode(session('resultado_sync'), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}
      </pre>
    @endif
  </div>
</div>
@endsection



@push('scripts')
<script>
(function(){
  const modal = document.getElementById('loading-modal');
  if (!modal) return;
  function showLoading() {
    modal.classList.remove('hidden');
    modal.classList.add('flex'); // centra con flex
  }

  // 1) Formularios con data-loading
  document.addEventListener('submit', function(e){
    const form = e.target;
    if (form && form.matches('form[data-loading]')) {
      showLoading();
    }
  }, true);

  // 2) Enlaces/botones con data-loading (para GETs)
  document.addEventListener('click', function(e){
    const link = e.target.closest('a[data-loading], button[data-loading]');
    if (link) {
      showLoading();
    }
  }, true);
})();
</script>
@endpush

