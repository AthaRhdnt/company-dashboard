<x-app-layout>
    <x-pages.form resource="item-specs">
        <div id="specs-container">
            <div class="form-row">
                <label for="item_description_0" class="block text-sm font-medium text-gray-700">Item Description:</label>
                <input type="text" id="item_description_0" name="item_specs[0][item_description]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">

                <label for="item_id_0" class="block text-sm font-medium text-gray-700 mt-4">Associated Item:</label>
                <select id="item_id_0" name="item_specs[0][item_id]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">Select an Item</option>
                    @foreach ($items as $item)
                        <option value="{{ $item->id }}">{{ $item->item_name }}</option>
                    @endforeach
                </select>
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
                const items = @json($items);

                addButton.addEventListener('click', function () {
                    const newDiv = document.createElement('div');
                    newDiv.classList.add('form-row', 'mt-4');

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