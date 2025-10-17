<x-app-layout>
    <x-pages.edit resource="vendors" :item="$vendor">
        <div>
            <label for="vendor_name" class="block text-sm font-medium text-gray-700">Vendor Name:</label>
            <input type="text" id="vendor_name" name="vendor_name" value="{{ $vendor->vendor_name }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>
    </x-pages.edit>
</x-app-layout>