<div class="bg-white p-4 rounded-lg shadow-md flex flex-col justify-between box-border">
    <div class="flex items-center box-border">
        <i class="fas fa-server text-gray-600 text-2xl md:text-4xl mr-3"></i>
        <div>
            <h2 class="text-md md:text-lg font-semibold text-gray-700">{{ $title }}</h2>
            <p class="text-xs md:text-sm text-gray-500">Estado actual del servicio</p>
        </div>
    </div>
    <div class="flex items-center mt-2">
        <span id="{{ $id }}" class="text-xs font-semibold px-2 py-0.5 rounded-full">
            Estado desconocido
        </span>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        updateConnectionStatus("{{ $id }}");

        setInterval(function() {
            console.log("Intento de conexion...");
            updateConnectionStatus("{{ $id }}");
        }, 60000); // 1 minuto
    });

    function updateConnectionStatus(elementId) {
        const checkUrl = "{{ route('check.connection.status') }}";

        $.getJSON(checkUrl)
            .done(function(data) {
                const $element = $("#" + elementId);
                let isConnected;

                if (elementId === 'academusoftStatus') {
                    isConnected = data.academusoft;
                } else if (elementId === 'brightspaceStatus') {
                    isConnected = data.brightspace;
                } else {
                    isConnected = false; 
                }

                $element.text(isConnected ? 'Conectado' : 'Sin conexión');

                if (isConnected) {
                    $element.removeClass('bg-red-100 text-red-800')
                            .addClass('bg-green-100 text-green-800');
                } else {
                    $element.removeClass('bg-green-100 text-green-800')
                            .addClass('bg-red-100 text-red-800');
                }
            })
            .fail(function() {
                const $element = $("#" + elementId);
                $element.text('Error en la conexión')
                        .removeClass('bg-green-100 text-green-800')
                        .addClass('bg-red-100 text-red-800');
            });
    }
</script>
