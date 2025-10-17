<x-app-layout>
    <x-pages.index
        resource="items"
        :items="$items"
        :headers="[
            'item_name' => 'Item Name',
            'item_price' => 'Item Price'
        ]"
    />
</x-app-layout>