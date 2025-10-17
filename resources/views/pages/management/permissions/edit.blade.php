<x-app-layout>
    <x-pages.form resource="permissions" action="update" :item="$permission">
        <div>
            <label for="permission_name" class="block text-sm font-medium text-gray-700">Name:</label>
            <input type="text" id="permission_name" name="permission_name" value="{{ $permission->permission_name }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>
        <div>
            <label for="level_id" class="block text-sm font-medium text-gray-700">Level:</label>
            <select id="level_id" name="level_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="">Select a Level</option>
                @foreach ($levels as $level)
                    <option value="{{ $level->id }}" @if ($level->id == $permission->level_id) selected @endif>{{ $level->level_name }}</option>
                @endforeach
            </select>
        </div>
    </x-pages.form>
</x-app-layout>