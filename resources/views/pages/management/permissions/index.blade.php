<x-app-layout>
    <x-pages.index
        resource="accesses"
        :items="$permissions"
        :headers="[
            'permission_name' => 'Access Name',
            'level.level_name' => 'Level'
        ]"
    />
</x-app-layout>