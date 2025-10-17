<x-app-layout>
    <x-pages.index
        resource="departments"
        :items="$departments"
        :headers="[
            'department_name' => 'Department Name',
            'department_code' => 'Department Code'
        ]"
    />
</x-app-layout>