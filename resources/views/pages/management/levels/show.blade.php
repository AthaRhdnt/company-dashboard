<x-app-layout>
    <x-pages.show
        resource="levels"
        :item="$level"
        :details="[
            'Level Name' => $level->level_name,
        ]"
    />
</x-app-layout>