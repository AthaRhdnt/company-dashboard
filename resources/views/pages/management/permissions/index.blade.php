<x-app-layout>
    <x-pages.index
        resource="permissions"
        :items="$permissions"
        :headers="[
            'permission_name' => 'Permissions Name',
            'level.level_name' => 'Level'
        ]"
    />
</x-app-layout>