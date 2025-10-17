<x-app-layout>
    <x-pages.index
        resource="item-specs"
        :items="$itemSpecs"
        :headers="[
            'item_description' => 'Item Description',
            'item.item_name' => 'Item Name'
        ]"
    />
</x-app-layout>