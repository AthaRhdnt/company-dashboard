<x-app-layout>
    <x-pages.show resource="items" :item="$item" :details="[
        'Item Name' => $item->item_name,
        'Item Price' => $item->item_price,
    ]"/>
</x-app-layout>