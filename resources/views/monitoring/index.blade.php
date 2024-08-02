@extends('layouts.main')

@section('title', 'Monitoreo sincronización con Academusoft y BrightSpace')

@section('content')
<div class="p-4 md:p-6">
    <div class="bg-gray-100 p-4 rounded-lg shadow-sm w-full mb-6">
        <div class="flex flex-col items-start md:items-end text-left md:text-right">
            <h1 class="text-2xl md:text-3xl font-semibold text-gray-800">Academusoft y BrightSpace</h1>
            <span class="text-lg md:text-xl text-gray-600">Monitoreo de sincronización</span>
            <div class="h-1 bg-blue-700 w-12 md:w-16 rounded mt-2"></div>
        </div>
    </div>

    <!-- Indicador de Conexión -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-6">
        <div class="bg-white p-4 rounded-lg shadow-md flex flex-col justify-between">
            <div class="flex items-center">
                <i class="fas fa-server text-gray-600 text-2xl md:text-4xl mr-3"></i>
                <div>
                    <h2 class="text-md md:text-lg font-semibold text-gray-700">Conexión con Academusoft</h2>
                    <p class="text-xs md:text-sm text-gray-500">Estado actual del servicio</p>
                </div>
            </div>
            <div class="flex items-center mt-2">
                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-0.5 rounded-full">Conectado</span>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-md flex flex-col justify-between">
            <div class="flex items-center">
                <i class="fas fa-server text-gray-600 text-2xl md:text-4xl mr-3"></i>
                <div>
                    <h2 class="text-md md:text-lg font-semibold text-gray-700">Conexión con BrightSpace</h2>
                    <p class="text-xs md:text-sm text-gray-500">Estado actual del servicio</p>
                </div>
            </div>
            <div class="flex items-center mt-2">
                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-0.5 rounded-full">Conectado</span>
            </div>
        </div>
    </div>

    <!-- Resumen General -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-6">
        <div class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
            <h2 class="text-base font-semibold text-gray-700 mb-1"><i class="fas fa-check-circle text-green-500"></i> <span class="text-gray-800">Tareas Completadas</span></h2>
            <p class="text-sm text-gray-500 mb-2">Progreso en la ejecución de tareas</p>
            <div class="relative w-24 md:w-32 h-24 md:h-32 mx-auto">
                <canvas id="completedTasksCanvas" class="w-full h-full"></canvas>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-xl md:text-2xl font-bold text-green-500">3/5</span>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
            <h2 class="text-base font-semibold text-gray-700 mb-1"><i class="fas fa-times-circle text-red-500"></i> <span class="text-gray-800">Tareas Fallidas</span></h2>
            <p class="text-sm text-gray-500 mb-2">Errores en la ejecución de tareas</p>
            <div class="relative w-24 md:w-32 h-24 md:h-32 mx-auto">
                <canvas id="failedTasksCanvas" class="w-full h-full"></canvas>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-xl md:text-2xl font-bold text-red-500">2/5</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle de Tareas -->
    <div class="bg-white p-4 md:p-6 rounded-lg shadow-md">
        <h2 class="text-lg font-semibold text-gray-700 mb-2">Detalle de Tareas de Sincronización</h2>
        <p class="text-sm text-gray-500 mb-4">Información detallada sobre la ejecución de tareas programadas</p>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2 md:p-4 text-left font-semibold text-gray-600">Tarea</th>
                        <th class="p-2 md:p-4 text-left font-semibold text-gray-600">Estado</th>
                        <th class="p-2 md:p-4 text-left font-semibold text-gray-600">Fecha y Hora de Finalización</th>
                        <th class="p-2 md:p-4 text-left font-semibold text-gray-600">Frecuencia de Ejecución</th>
                        <th class="p-2 md:p-4 text-left font-semibold text-gray-600">Detalle</th>
                        <th class="p-2 md:p-4 text-left font-semibold text-gray-600">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b hover:bg-gray-50 transition-colors duration-300">
                        <td class="p-2 md:p-4">Creación de usuarios</td>
                        <td class="p-2 md:p-4"><span class="bg-green-100 text-green-800 text-xs font-semibold mr-2 px-2 py-0.5 rounded-full">Completado</span></td>
                        <td class="p-2 md:p-4">2024-07-02 14:30:00</td>
                        <td class="p-2 md:p-4"><span class="text-gray-700">Diaria</span></td>
                        <td class="p-2 md:p-4"><span class="text-lg font-bold text-gray-700">50</span> <span class="text-gray-500">usuarios creados</span></td>
                        <td class="p-2 md:p-4">
                            <button class="bg-gray-200 text-gray-700 text-xs px-3 py-1 rounded hover:bg-gray-300 transition-colors duration-300">Ver Detalles</button>
                        </td>
                    </tr>
                    <tr class="border-b hover:bg-gray-50 transition-colors duration-300">
                        <td class="p-2 md:p-4">Creación de cursos</td>
                        <td class="p-2 md:p-4"><span class="bg-green-100 text-green-800 text-xs font-semibold mr-2 px-2 py-0.5 rounded-full">Completado</span></td>
                        <td class="p-2 md:p-4">2024-07-03 10:15:00</td>
                        <td class="p-2 md:p-4"><span class="text-gray-700">Semanal</span></td>
                        <td class="p-2 md:p-4"><span class="text-lg font-bold text-gray-700">10</span> <span class="text-gray-500">cursos creados</span></td>
                        <td class="p-2 md:p-4">
                            <button class="bg-gray-200 text-gray-700 text-xs px-3 py-1 rounded hover:bg-gray-300 transition-colors duration-300">Ver Detalles</button>
                        </td>
                    </tr>
                    <tr class="border-b hover:bg-gray-50 transition-colors duration-300">
                        <td class="p-2 md:p-4">Calificación de un estudiante por cursos</td>
                        <td class="p-2 md:p-4"><span class="bg-red-100 text-red-800 text-xs font-semibold mr-2 px-2 py-0.5 rounded-full whitespace-nowrap">Tarea Fallida</span></td>
                        <td class="p-2 md:p-4">2024-07-04 13:20:00</td>
                        <td class="p-2 md:p-4"><span class="text-gray-700">Por corte (3 cortes por semestre)</span></td>
                        <td class="p-2 md:p-4"><span class="text-lg font-bold text-gray-700">N/A</span> <span class="text-gray-500">Error en la calificación</span></td>
                        <td class="p-2 md:p-4">
                            <button class="bg-green-500 text-white text-xs px-3 py-1 rounded hover:bg-green-600 transition-colors duration-300" onclick="openModal()">Reintentar</button>
                        </td>
                    </tr>
                    <tr class="border-b hover:bg-gray-50 transition-colors duration-300">
                        <td class="p-2 md:p-4">Cierre de periodo</td>
                        <td class="p-2 md:p-4"><span class="bg-red-100 text-red-800 text-xs font-semibold mr-2 px-2 py-0.5 rounded-full whitespace-nowrap">Tarea Fallida</span></td>
                        <td class="p-2 md:p-4">2024-07-05 16:00:00</td>
                        <td class="p-2 md:p-4"><span class="text-gray-700">Semestral</span></td>
                        <td class="p-2 md:p-4"><span class="text-lg font-bold text-gray-700">N/A</span> <span class="text-gray-500">N/A</span></td>
                        <td class="p-2 md:p-4">
                            <button class="bg-green-500 text-white text-xs px-3 py-1 rounded hover:bg-green-600 transition-colors duration-300" onclick="openModal()">Reintentar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="syncModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-11/12 md:w-1/2">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Progreso de Sincronización</h2>
        <div class="relative pt-1">
            <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-green-200">
                <div id="syncProgress" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500 transition-all duration-500" style="width: 0%"></div>
            </div>
            <div class="flex justify-center items-center text-3xl font-bold text-green-600">
                <i class="fas fa-circle-notch fa-spin mr-2"></i>
                <span id="progressPercent">0%</span>
            </div>
        </div>
        <div class="text-right mt-4">
            <button onclick="closeModal()" class="bg-gray-200 text-gray-700 text-xs px-3 py-1 rounded hover:bg-gray-300 transition-colors duration-300">Cerrar</button>
        </div>
    </div>
</div>

<!-- Incluyendo FontAwesome para iconos -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Script para gráficos de progreso individual
    const renderProgressChart = (canvasId, percentage, color) => {
        const ctx = document.getElementById(canvasId).getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [percentage, 100 - percentage],
                    backgroundColor: [color, '#e5e7eb'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '75%',
                rotation: -90,
                circumference: 360,
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: { enabled: false },
                }
            }
        });
    };

    // Datos de ejemplo
    renderProgressChart('completedTasksCanvas', 60, '#38a169'); // 3/5 completado
    renderProgressChart('failedTasksCanvas', 40, '#e53e3e'); // 2/5 fallido

    // Script para el modal
    function openModal() {
        document.getElementById('syncModal').classList.remove('hidden');
        simulateSyncProgress();
    }

    function closeModal() {
        document.getElementById('syncModal').classList.add('hidden');
        document.getElementById('syncProgress').style.width = '0%';
        document.getElementById('progressPercent').innerText = '0%';
    }

    function simulateSyncProgress() {
        let progress = 0;
        const interval = setInterval(() => {
            progress += 10;
            document.getElementById('syncProgress').style.width = `${progress}%`;
            document.getElementById('progressPercent').innerText = `${progress}%`;

            if (progress >= 100) {
                clearInterval(interval);
                setTimeout(() => {
                    alert('Sincronización completada.');
                    closeModal();
                }, 500);
            }
        }, 500);
    }
</script>
@endsection
