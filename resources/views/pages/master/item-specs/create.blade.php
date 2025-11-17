<x-app-layout>
    <x-pages.form resource="item-specs">
        {{-- If selectedItem exists, pre-populate the item selector --}}
        @if (isset($selectedItem))
            <input type="hidden" name="item_specs[0][item_id]" value="{{ $selectedItem->id }}">
        @endif
        
        <div id="specs-container">
            <div class="form-row">
                <label for="item_description_0" class="block text-sm font-medium text-gray-700">Item Description:</label>
                <input type="text" id="item_description_0" name="item_specs[0][item_description]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">

                <label for="item_id_0" class="block text-sm font-medium text-gray-700 mt-4">Associated Item:</label>
                <select id="item_id_0" name="item_specs[0][item_id]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    {{ isset($selectedItem) ? 'disabled' : '' }}>
                    <option value="">Select an Item</option>
                    @foreach ($items as $item)
                        <option value="{{ $item->id }}" {{ (isset($selectedItem) && $selectedItem->id == $item->id) ? 'selected' : '' }}>
                            {{ $item->item_name }}
                        </option>
                    @endforeach
                </select>
                
                @if (isset($selectedItem))
                    <p class="text-sm text-indigo-600 mt-1">This specification will be linked to Item: {{ $selectedItem->item_name }} (Disabled)</p>
                @endif
            </div>
        </div>

        <x-primary-button type="button" id="add-spec-btn" class="mt-4">
            Add Another Spec
        </x-primary-button>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let specIndex = 1;
                const container = document.getElementById('specs-container');
                const addButton = document.getElementById('add-spec-btn');
                const items = @json($items); // Pass all items to JS for dynamic dropdown population

                // If an item is pre-selected, we need to adjust the initial index count
                @if (isset($selectedItem))
                    // We already have index 0 populated, so start dynamic adding at 1
                    specIndex = 1;
                    // Ensure the first row for the pre-selected item is correctly named for index 0
                    document.querySelector('#specs-container > div.form-row > input[name="item_specs[0][item_description]"]').name = 'item_specs[0][item_description]';
                    document.querySelector('#specs-container > div.form-row > select[name="item_specs[0][item_id]"]') ? 
                        document.querySelector('#specs-container > div.form-row > select[name="item_specs[0][item_id]"]').name = 'item_specs[0][item_id]' : null;
                @else
                    // If no item is pre-selected, the first dynamically added row starts at index 1, so index 0 is the first new row.
                    specIndex = 1; 
                @endif


                addButton.addEventListener('click', function () {
                    const newDiv = document.createElement('div');
                    newDiv.classList.add('form-row', 'mt-6', 'border-t', 'pt-4'); // Added border/padding for visual separation

                    newDiv.innerHTML = `
                        <label for="item_description_${specIndex}" class="block text-sm font-medium text-gray-700">Item Description:</label>
                        <input type="text" id="item_description_${specIndex}" name="item_specs[${specIndex}][item_description]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">

                        <label for="item_id_${specIndex}" class="block text-sm font-medium text-gray-700 mt-4">Associated Item:</label>
                        <select id="item_id_${specIndex}" name="item_specs[${specIndex}][item_id]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Select an Item</option>
                            ${items.map(item => `<option value="${item.id}">${item.item_name}</option>`).join('')}
                        </select>
                    `;
                    container.appendChild(newDiv);
                    specIndex++;
                });
            });
        </script>
    </x-pages.form>
</x-app-layout>