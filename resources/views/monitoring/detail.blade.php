@extends('layouts.main')

@section('title', 'Detalle de Tarea | JanusSync')

@section('content')

<div class="w-full max-w-full p-4 md:p-6 overflow-hidden box-border">
    <!-- Header Section -->
    <div class="bg-gray-100 p-4 rounded-lg shadow-sm w-full mb-6 box-border">
        <h1 class="text-2xl md:text-3xl font-semibold text-gray-800">Detalle de Tarea: {{ $scheduledTask->task_name }}</h1>
        <span class="text-lg md:text-xl text-gray-600">Sincronización - {{ $scheduledTask->frequency }}</span>
        <div class="h-1 bg-blue-700 w-12 md:w-16 rounded mt-2"></div>
    </div>

    <!-- Detalle según el tipo de acción -->
    <div class="bg-white p-4 md:p-6 rounded-lg shadow-md box-border">
        @include($view, ['details' => $details])
    </div>
</div>

@endsection
