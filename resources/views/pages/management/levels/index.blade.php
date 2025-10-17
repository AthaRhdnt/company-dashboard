<x-app-layout>
    <x-pages.index
        resource="levels"
        :items="$levels"
        :headers="[
            'level_name' => 'Level',
        ]"
    />
</x-app-layout>