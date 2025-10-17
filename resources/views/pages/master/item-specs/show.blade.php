<x-app-layout>
    <x-pages.show resource="item-specs" :item="$itemSpec" :details="[
        'Item Description' => $itemSpec->item_description,
        'Associated Item' => $itemSpec->item->item_name,
    ]"/>
</x-app-layout>