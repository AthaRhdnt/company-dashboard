<x-app-layout>
    <x-pages.form resource="taxes" :item="$tax">
        <div>
            <label for="tax_name" class="block text-sm font-medium text-gray-700">Tax Name:</label>
            <input type="text" id="tax_name" name="tax_name" value="{{ $tax->tax_name }}" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>
        <div class="mt-4">
            <label for="tax_percentage" class="block text-sm font-medium text-gray-700">Tax Percentage:</label>
            <input type="number" {{-- Recommended: Change to 'number' for percentage input --}} step="0.01" {{-- Allows decimal input --}} id="tax_percentage"
                name="tax_percentage" value="{{ $tax->tax_percentage }}" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>
    </x-pages.form>
</x-app-layout>
