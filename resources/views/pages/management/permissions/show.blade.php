<x-app-layout>
    <x-pages.show
        resource="accesses"
        :item="$accesses"
        :details="[
            'Permission Name' => $accesses->permission_name,
            'Level' => $accesses->level->level_name ?? 'N/A'
        ]"
    />
</x-app-layout>