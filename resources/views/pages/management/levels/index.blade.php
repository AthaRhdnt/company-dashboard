<x-app-layout>
    <x-pages.index
        resource="roles"
        :items="$roles"
        :headers="[
            'level_name' => 'Role',
            'permission_list' => 'Role Access',
        ]"
    />
</x-app-layout>