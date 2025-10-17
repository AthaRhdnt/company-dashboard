line_item_new_row_template
@props(['items', 'currency'])
@php
    // $placeholder is the literal string 'item.id', which Alpine uses to access the loop index.
    $placeholder = 'item.id'; 
    $items = $items ?? collect();
    $currency = $currency ?? 'CUR';
@endphp

<tr class="hover:bg-gray-50 bg-indigo-50/50" :id="'line-item-' + item.id">
    
    {{-- Item Name/Specs --}}
    <td class="px-6 py-4 text-sm font-medium text-gray-900 space-y-2">
        <div class="space-y-1">
            <x-input-label value="Item Name" class="!text-xs font-bold" />
            
            {{-- Editable Item Select --}}
            <select x-on:change="
                    // 1. Update specs
                    updateAvailableSpecs(item.id, $event.target.value, true);
                    
                    // 2. Look up the selected item's data and get the base price
                    let selectedItem = {{ json_encode($items->keyBy('id')->toArray()) }}[$event.target.value];
                    
                    // 3. Store the item's 'item_price' into the Alpine object's 'unit_price' property
                    let price = selectedItem ? parseFloat(selectedItem.item_price) : 0.00;
                    item.unit_price = price;
                    
                    // 4. Update subtotal based on new item price and current quantity
                    item.subtotal = price * item.quantity;
                "
                :name="'new_line_items[' + {{ $placeholder }} + '][item_id]'" 
                x-model="item.item_id"
                class="block w-full text-sm rounded-md shadow-sm p-1 transition duration-150 bg-yellow-100 border-2 border-yellow-500 focus:border-red-500 focus:ring-yellow-400" required>
                
                <option value="">Select Item</option>
                @foreach ($items as $item)
                    <option value="{{ $item->id }}" x-text="'{{ $item->item_name }}'"></option>
                @endforeach
            </select>
        </div>

        {{-- Editable Specs (Multi-select) --}}
        <div class="mt-2 space-y-1">
            <x-input-label value="Specs/Description" class="!text-xs font-bold" />
            
            <select :name="'new_line_items[' + {{ $placeholder }} + '][specs][]'"
                x-model="item.specs"
                multiple
                class="block w-full text-xs rounded-md shadow-sm p-1 transition duration-150 bg-yellow-100 border-2 border-yellow-500 focus:border-red-500 focus:ring-yellow-400 h-20">
                
                <template x-for="spec in item.availableSpecs" :key="spec.id">
                    <option :value="spec.id" x-text="spec.item_description"></option>
                </template>
                
            </select>
            <p class="text-xs text-gray-500 mt-1">Hold CTRL/CMD to select multiple specs.</p>
        </div>
    </td>
    
    {{-- Unit Price (FIXED: Always displays item.unit_price) --}}
    <td class="px-6 py-2 text-sm text-right text-gray-700">
        <span class="font-semibold" 
            x-text="
                '{{ $currency }} ' + formatCurrencyCustom(
                    // FIX: Directly display the stored unit_price, which comes from item->item_price
                    item.unit_price
                )
            ">
            0.00 (New)
        </span>
    </td>

    {{-- Editable Quantity (REACTIVITY ADDED) --}}
    <td class="px-6 py-2 text-sm text-gray-500">
        <input type="number" 
            x-model.number="item.quantity"
            :name="'new_line_items[' + {{ $placeholder }} + '][quantity]'"
            min="0"
            step="1"
            required
            class="block w-20 text-sm rounded-md shadow-sm p-1 text-center bg-yellow-100 border-2 border-yellow-500 focus:border-red-500 focus:ring-yellow-400"
            
            {{-- FIX: Add input handler to calculate subtotal based on fixed price --}}
            x-on:input.debounce.150ms="item.subtotal = (item.quantity * item.unit_price).toFixed(2)">
    </td>

    {{-- Editable Subtotal --}}
    <td class="px-6 py-2 text-sm text-right font-medium text-gray-700">
        <input type="number" 
            x-model.number="item.subtotal"
            :name="'new_line_items[' + {{ $placeholder }} + '][subtotal]'"
            min="0"
            step="0.01"
            required
            class="block w-32 ml-auto text-sm rounded-md shadow-sm p-1 text-right bg-yellow-100 border-2 border-yellow-500 focus:border-red-500 focus:ring-yellow-400">
    </td>
    
    {{-- Action (Remove Button) --}}
    <td class="px-6 py-4 text-sm text-gray-500">
        <button type="button" @click="removeLineItem(item.id, true)" class="text-red-600 hover:text-red-900 text-xs">
            Remove
        </button>
    </td>
</tr>