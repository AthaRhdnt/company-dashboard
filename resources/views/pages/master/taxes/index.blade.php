<x-app-layout>
    <x-pages.index
        resource="taxes"
        :items="$taxes"
        :headers="[
            'tax_name' => 'Tax Name',
            'tax_percentage' => 'Tax Percentage (%)'
        ]"
    />
</x-app-layout>