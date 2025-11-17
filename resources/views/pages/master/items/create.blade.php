<x-app-layout>
    <x-pages.form resource="items">
        {{-- ITEM DETAILS --}}
        <div>
            <label for="item_name" class="block text-sm font-medium text-gray-700">Item Name:</label>
            <x-text-input type="text" id="item_name" name="item_name" required value="{{ old('item_name') }}" />
        </div>

        <div class="mt-4">
            <label for="item_price" class="block text-sm font-medium text-gray-700">Item Price:</label>
            <x-text-input type="number" id="item_price" name="item_price" step="0.01" min="0" required
                value="{{ old('item_price') }}" />
        </div>

        <hr class="my-6">

        {{-- WRAP with x-data --}}
        <div x-data="{ showSpecs: {{ old('has_specs') ? 'true' : 'false' }} }" x-cloak>
            <div class="flex items-center mb-4">
                <input type="checkbox" id="has_specs" name="has_specs" x-model="showSpecs"
                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <label for="has_specs" class="ml-2 text-sm text-gray-700 font-medium">Add Item Specifications</label>
            </div>

            <div x-show="showSpecs" x-transition class="mt-4">
                <h2 class="text-xl font-bold mb-4">Item Specifications</h2>

                <div id="specs-container">
                    @include('pages.master.items.partials.spec-field', ['index' => 0, 'spec' => null])

                    <template id="spec-template">
                        @include('pages.master.items.partials.spec-field', [
                            'index' => 'INDEX_PLACEHOLDER',
                            'spec' => null,
                        ])
                    </template>
                </div>

                <x-primary-button type="button" id="add-spec-btn" class="mt-4">Add Another Spec</x-primary-button>
            </div>
        </div>

        {{-- keep your JS to add/remove rows unchanged --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let specIndex = 1;
                const container = document.getElementById('specs-container');
                const addButton = document.getElementById('add-spec-btn');
                const template = document.getElementById('spec-template');

                addButton && addButton.addEventListener('click', function() {
                    const newRowHtml = template.innerHTML.replace(/INDEX_PLACEHOLDER/g, specIndex);
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = newRowHtml.trim();
                    // ensure we're appending the actual row element (partial should have .spec-row wrapper)
                    container.appendChild(tempDiv.firstElementChild || tempDiv.firstChild);
                    specIndex++;
                });

                container && container.addEventListener('click', function(e) {
                    if (e.target.classList.contains('remove-spec-btn')) {
                        e.target.closest('.spec-row')?.remove();
                    }
                });
            });
        </script>
    </x-pages.form>
</x-app-layout>
