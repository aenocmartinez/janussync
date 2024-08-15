<div class="mt-6 flex flex-col items-center">
    <div class="text-xs text-gray-600 mb-2">
        Mostrando <span id="startRecord">{{ $startRecord }}</span> a <span id="endRecord">{{ $endRecord }}</span> de <span id="totalRecords">{{ $totalRecords }}</span> registros
    </div>
    <div class="flex items-center space-x-2">
        <button class="px-3 py-1 border border-transparent bg-blue-600 text-white text-xs font-medium rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="previousPage()" aria-label="Previous">
            <i class="fas fa-chevron-left"></i>
        </button>
        <select id="pageSelect" class="border border-gray-300 bg-white text-gray-700 text-xs rounded-full focus:outline-none focus:ring-blue-500 focus:border-blue-500 transition ease-in-out duration-150" onchange="gotoPage(this.value)">
            @for ($i = 1; $i <= $totalPages; $i++)
                <option value="{{ $i }}" @if($currentPage == $i) selected @endif>{{ $i }}</option>
            @endfor
        </select>
        <button class="px-3 py-1 border border-transparent bg-blue-600 text-white text-xs font-medium rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="nextPage()" aria-label="Next">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>

<script>
let currentPage = {{ $currentPage }};
const totalPages = {{ $totalPages }};
const recordsPerPage = {{ $recordsPerPage }};
const totalRecords = {{ $totalRecords }};

function updatePagination() {
    const startRecord = (currentPage - 1) * recordsPerPage + 1;
    const endRecord = Math.min(currentPage * recordsPerPage, totalRecords);

    document.getElementById('startRecord').textContent = startRecord;
    document.getElementById('endRecord').textContent = endRecord;

    document.getElementById('pageSelect').value = currentPage;
}

function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        window.location.href = `?page=${currentPage}`;
    }
}

function nextPage() {
    if (currentPage < totalPages) {
        currentPage++;
        window.location.href = `?page=${currentPage}`;
    }
}

function gotoPage(page) {
    currentPage = parseInt(page);
    window.location.href = `?page=${currentPage}`;
}

document.addEventListener('DOMContentLoaded', function () {
    updatePagination();
});
</script>
