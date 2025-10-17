<x-app-layout>
    <x-pages.show resource="vendors" :item="$vendor" :details="[
        'Vendor Name' => $vendor->vendor_name,
    ]"/>
</x-app-layout>