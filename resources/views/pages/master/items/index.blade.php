<x-app-layout>
    <x-pages.index
        resource="items"
        :items="$items"
        :headers="[
            'item_name' => 'Item Name',
            'item_price' => 'Item Price',
            'item_spec' => 'Item Spec Detail' // This now works via the Accessor
        ]"
    />
</x-app-layout>