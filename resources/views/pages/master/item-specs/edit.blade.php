<x-app-layout>
    <x-pages.form resource="item-specs" action="update" :item="$itemSpec">
        <div>
            <label for="item_description" class="block text-sm font-medium text-gray-700">Item Description:</label>
            <input type="text" id="item_description" name="item_description" value="{{ old('item_description', $itemSpec->item_description) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>
        <div class="mt-4">
            <label for="item_id" class="block text-sm font-medium text-gray-700">Associated Item:</label>
            <select id="item_id" name="item_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="">Select an Item</option>
                @foreach ($items as $item)
                    <option value="{{ $item->id }}" {{ old('item_id', $itemSpec->item_id) == $item->id ? 'selected' : '' }}>{{ $item->item_name }}</option>
                @endforeach
            </select>
        </div>
    </x-pages.form>
</x-app-layout>