<x-app-layout>
    <x-pages.form resource="clients" action="update" :item="$client">
        <div>
            <label for="client_name" class="block text-sm font-medium text-gray-700">Client Name:</label>
            <input type="text" id="client_name" name="client_name" value="{{ $client->client_name }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>
        <div class="mt-4">
            <label for="address" class="block text-sm font-medium text-gray-700">Address:</label>
            <input type="text" id="address" name="address" value="{{ $client->address }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>
        <div class="mt-4">
            <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number:</label>
            <input type="text" id="phone_number" name="phone_number" value="{{ $client->phone_number }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>
        <div class="mt-4">
            <label for="fax_number" class="block text-sm font-medium text-gray-700">Fax Number:</label>
            <input type="text" id="fax_number" name="fax_number" value="{{ $client->fax_number }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>
        <div class="mt-4">
            <label for="contact_person_name" class="block text-sm font-medium text-gray-700">Contact Person Name:</label>
            <input type="text" id="contact_person_name" name="contact_person_name" value="{{ $client->contact_person_name ?? "N/A" }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>
    </x-pages.form>
</x-app-layout>