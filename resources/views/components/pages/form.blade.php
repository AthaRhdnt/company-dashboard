@props(['resource', 'action' => 'store', 'item' => null])

<x-pages.layout>
    <h1 class="text-2xl font-bold text-gray-800 mb-6 capitalize">
        {{ $action === 'store' ? 'Create New' : 'Edit' }} {{ str(Str::singular($resource))->headline() }}
    </h1>

    @php
        $route = ($action === 'store') ? route($resource . '.store') : route($resource . '.update', $item->id);
    @endphp

    <form action="{{ $route }}" method="POST">
        @csrf
        @if($action === 'update')
            @method('PUT')
        @endif

        {{ $slot }}

        <div class="mt-6 flex justify-end space-x-2">
            <a href="{{ route($resource . '.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md font-semibold text-sm hover:bg-gray-300">
                Cancel
            </a>
            <x-secondary-button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ $action === 'store' ? 'Create' : 'Update' }} {{ str(Str::singular($resource))->headline() }}
            </x-secondary-button>
        </div>
    </form>
</x-pages.layout>