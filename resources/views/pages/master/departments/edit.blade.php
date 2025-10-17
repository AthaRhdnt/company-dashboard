<x-app-layout>
    <x-pages.form resource="departments" :item="$department">
        <div>
            <label for="department_name" class="block text-sm font-medium text-gray-700">Department Name:</label>
            <input type="text" id="department_name" name="department_name" value="{{ $department->department_name }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>
        <div class="mt-4">
            <label for="department_code" class="block text-sm font-medium text-gray-700">Department Code:</label>
            <input type="text" id="department_code" name="department_code" value="{{ $department->department_code }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>
    </x-pages.form>
</x-app-layout>