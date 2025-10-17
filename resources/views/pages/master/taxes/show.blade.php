<x-app-layout>
    <x-pages.show resource="taxes" :item="$tax" :details="[
        'Tax Name' => $tax->tax_name,
        'Tax Percentage' => $tax->tax_percentage . ' %',
    ]"/>
</x-app-layout>