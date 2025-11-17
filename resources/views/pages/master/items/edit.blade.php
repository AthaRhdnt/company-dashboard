<x-app-layout>
    <x-pages.form resource="items" action="update" :item="$item">
        {{-- ITEM DETAILS SECTION (Same as current item edit) --}}
        <div>
            <label for="item_name" class="block text-sm font-medium text-gray-700">Item Name:</label>
            <x-text-input type="text" id="item_name" name="item_name" value="{{ old('item_name', $item->item_name) }}" required />
        </div>
        <div class="mt-4">
            <label for="item_price" class="block text-sm font-medium text-gray-700">Item Price:</label>
            <x-text-input type="number" id="item_price" name="item_price" value="{{ old('item_price', $item->item_price) }}" step="0.01" min="0" required />
        </div>

        <hr class="my-6">

        {{-- ITEM SPECS DYNAMIC SECTION --}}
        <h2 class="text-xl font-bold mb-4">Item Specifications</h2>
        
        <div id="specs-container">
            {{-- Existing Specs --}}
            @foreach($item->itemSpecs as $index => $spec)
                @include('pages.master.items.partials.spec-field', ['index' => $index, 'spec' => $spec])
            @endforeach
        </div>

        <x-primary-button type="button" id="add-spec-btn" class="mt-4">
            Add New Spec
        </x-primary-button>
        
        {{-- @push('scripts') --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Start the index for *new* specs after the existing ones
                let specIndex = {{ $item->itemSpecs->count() }}; 
                const container = document.getElementById('specs-container');
                const addButton = document.getElementById('add-spec-btn');
                
                // Template for NEW specs
                const newSpecTemplate = `
                    <div class="spec-row flex items-center space-x-2 mt-2 p-2 border border-gray-200 rounded-md">
                        <div class="flex-grow">
                            <input type="text" name="new_specs[INDEX_PLACEHOLDER][item_description]" placeholder="Specification Description" class="w-full mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        </div>
                        <button type="button" class="remove-spec-btn text-red-500 hover:text-red-700 p-2">&times;</button>
                    </div>
                `;

                addButton.addEventListener('click', function () {
                    const newRow = newSpecTemplate.replace(/INDEX_PLACEHOLDER/g, specIndex);
                    
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = newRow.trim();
                    container.appendChild(tempDiv.firstChild);

                    specIndex++;
                });

                // Handle removal of specs (both existing and newly added)
                container.addEventListener('click', function(e) {
                    if (e.target.classList.contains('remove-spec-btn')) {
                        e.target.closest('.spec-row').remove();
                    }
                });
            });
        </script>
        {{-- @endpush --}}
    </x-pages.form>
</x-app-layout>