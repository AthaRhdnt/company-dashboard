<x-app-layout>
    <x-pages.index
        resource="users"
        :items="$users"
        :headers="[
            // 'id' => 'ID',
            'name' => 'Name',
            'email' => 'Email',
            'level.level_name' => 'Level'
        ]"
    />
</x-app-layout>