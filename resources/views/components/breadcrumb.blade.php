<nav class="breadcrumb mb-4" aria-label="Breadcrumb">
    <ol class="list-reset flex text-gray-700">
        @foreach($items as $item)
            <li>
                @if($loop->last)
                    <span class="text-gray-500">{{ $item['name'] }}</span>
                @else
                    <a href="{{ $item['url'] }}" class="text-blue-600 hover:underline">{{ $item['name'] }}</a>
                    <!-- <span class="mx-2">/</span> -->
                @endif
            </li>
        @endforeach
    </ol>
</nav>
