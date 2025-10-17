<x-app-layout>
    <x-pages.show
        resource="users"
        :item="$user"
        :details="[
            'Name' => $user->name,
            'Email' => $user->email,
            'Level' => $user->level->level_name ?? 'N/A'
        ]"
    />
</x-app-layout>