<x-app-layout>
    <x-pages.show resource="departments" :item="$department" :details="[
        'Department Name' => $department->department_name,
        'Department Code' => $department->department_code,
    ]"/>
</x-app-layout>