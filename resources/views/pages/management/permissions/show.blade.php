<x-app-layout>
    <x-pages.show
        resource="permissions"
        :item="$permission"
        :details="[
            'Permission Name' => $permission->permission_name,
            'Level' => $permission->level->level_name ?? 'N/A'
        ]"
    />
</x-app-layout>