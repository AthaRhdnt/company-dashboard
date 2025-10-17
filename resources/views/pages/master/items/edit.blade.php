<x-app-layout>
    <x-pages.form resource="items" :item="$item">
        <div>
            <label for="item_name" class="block text-sm font-medium text-gray-700">Item Name:</label>
            <input type="text" id="item_name" name="item_name" value="{{ $item->item_name }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>
        <div class="mt-4">
            <label for="item_price" class="block text-sm font-medium text-gray-700">Item Price:</label>
            <input type="number" id="item_price" name="item_price" value="{{ $item->item_price }}" step="0.01" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>
    </x-pages.form>
</x-app-layout>