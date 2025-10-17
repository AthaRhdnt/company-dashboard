@props(['resource', 'items', 'headers'])

{{-- Determine if the items collection is a Paginator instance --}}
@php
    $isPaginator = $items instanceof \Illuminate\Contracts\Pagination\Paginator;
@endphp

<x-pages.layout>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold capitalize">{{ str($resource)->headline() }} List</h1>
        <a href="{{ route($resource . '.create') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">Add New {{ str(Str::singular($resource))->headline() }}</a>
    </div>

    {{-- Use count() instead of isNotEmpty() to ensure it works for both Collection and Paginator --}}
    @if($items->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">No</th>
                        @foreach($headers as $header)
                            <th class="py-2 px-4 border-b">{{ $header }}</th>
                        @endforeach
                        <th class="py-2 px-4 border-b">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- CONDITIONAL INDEX CALCULATION --}}
                    @php
                        $startNumber = 1;
                        if ($isPaginator) {
                            $startNumber = ($items->currentPage() - 1) * $items->perPage() + 1;
                        }
                    @endphp
                    
                    @foreach($items as $index => $item)
                        <tr>
                            {{-- Use the calculated start number plus the current index --}}
                            <td class="py-2 px-4 border-b">{{ $startNumber + $index }}</td>
                            
                            @foreach($headers as $key => $header)
                                <td class="py-2 px-4 border-b">{{ data_get($item, $key) }}</td>
                            @endforeach
                            <td class="py-2 px-4 border-b">
                                <a href="{{ route($resource . '.show', $item->id) }}" class="text-blue-500 hover:underline mr-2">View</a>
                                <a href="{{ route($resource . '.edit', $item->id) }}" class="text-yellow-500 hover:underline mr-2">Edit</a>
                                <form action="{{ route($resource . '.destroy', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:underline" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- CONDITIONAL PAGINATION LINKS --}}
        @if ($isPaginator)
            <div class="mt-4">
                {{ $items->links() }}
            </div>
        @endif

    @else
        <p>No {{ $resource }} found.</p>
    @endif
</x-pages.layout>