@extends('layouts.main')

@section('title', 'Editar Tarea Programada | JanusSync')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div class="flex items-center mb-4 sm:mb-0">
            <i class="fas fa-edit text-blue-800 text-2xl mr-2"></i>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-blue-800">Editar Tarea Programada</h1>
        </div>
    </div>

    <div class="bg-white shadow-lg rounded-lg p-6">
        <form action="{{ route('scheduled-tasks.update', $scheduledTask->id) }}" method="POST">
            @csrf
            @method('PUT')

            @include('scheduled_tasks.partials.form', ['scheduledTask' => $scheduledTask])

            <div class="mt-6 flex justify-end">
                <a href="{{ route('scheduled-tasks.index') }}" class="mr-4 px-4 py-2 bg-gray-300 text-gray-800 rounded-lg shadow hover:bg-gray-400 transition duration-150">
                    Cancelar
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition duration-150">
                    Actualizar Tarea
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
