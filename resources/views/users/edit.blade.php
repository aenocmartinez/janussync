@extends('layouts.main')

@section('title', 'Actualizar Usuario | JanusSync')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-4xl">
        <div class="bg-white shadow-lg rounded-lg p-8">
            <h2 class="text-3xl font-semibold text-center text-blue-800 mb-6">{{ __('Actualizar Usuario') }}</h2>
            
            <form method="POST" action="{{ route('users.update', $user->id) }}">
                @csrf
                @method('PUT')

                @include('users.form', ['user' => $user]) <!-- Incluir el formulario reutilizable con datos -->

            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Incluir jQuery desde un CDN -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Incluir Select2 desde un CDN -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('#role').select2({
            placeholder: 'Seleccione un Rol',
            allowClear: true
        });

        $('#password').on('input', function() {
            const password = $(this).val();
            const lengthCheck = password.length >= 8;
            const uppercaseCheck = /[A-Z]/.test(password);
            const lowercaseCheck = /[a-z]/.test(password);
            const numberCheck = /[0-9]/.test(password);
            const specialCheck = /[.+\-_\!\¡\(\)\?\¿\$#]/.test(password);

            $('#length-check').toggleClass('text-green-600', lengthCheck).toggleClass('text-gray-500', !lengthCheck);
            $('#uppercase-check').toggleClass('text-green-600', uppercaseCheck).toggleClass('text-gray-500', !uppercaseCheck);
            $('#lowercase-check').toggleClass('text-green-600', lowercaseCheck).toggleClass('text-gray-500', !lowercaseCheck);
            $('#number-check').toggleClass('text-green-600', numberCheck).toggleClass('text-gray-500', !numberCheck);
            $('#special-check').toggleClass('text-green-600', specialCheck).toggleClass('text-gray-500', !specialCheck);
        });
    });
</script>
@endpush
