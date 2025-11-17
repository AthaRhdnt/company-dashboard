<x-app-layout>
    <x-pages.show
        resource="roles"
        :item="$role"
        :details="[
            'Level Name' => $role->level_name,
        ]"
    />
</x-app-layout>