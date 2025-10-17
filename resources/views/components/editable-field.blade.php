@props(['label', 'isEditing', 'type' => 'text', 'name', 'value', 'displayValue'])

<div class="space-y-1">
    {{-- Label --}}
    <x-input-label :for="$name" :value="__($label)" />

    {{-- Static Display Value --}}
    <p x-show="!{{ $isEditing }}" class="text-sm text-gray-900 font-medium p-2">
        {{ $displayValue }}
    </p>

    {{-- Editable Input Field (CRITICAL FIX APPLIED HERE) --}}
    <input x-show="{{ $isEditing }}" 
        type="{{ $type }}" 
        id="{{ $name }}"
        name="{{ $name }}" 
        value="{{ old($name, $value) }}"
        {{-- Set required for date fields when editing --}}
        {{ $attributes->merge(['required' => $type === 'date']) }}
        
        {{-- CRITICAL FIX: All classes (Base, Active, Error) are now inside the dynamic :class --}}
        :class="{
            // Base Form Classes (Always applied)
            'block w-full text-sm rounded-md shadow-sm p-2 transition duration-150 focus:ring focus:ring-opacity-50': true,
            
            // EDITING MODE: Prominent yellow highlight
            'bg-yellow-100 border-2 border-yellow-500 focus:border-red-500 focus:ring-yellow-400': {{ $isEditing }},
            
            // NON-EDITING MODE: Standard gray border
            'border-gray-300 focus:border-indigo-300 focus:ring-indigo-200': !({{ $isEditing }}),
            
            // Error Styling
            'border-red-500 focus:border-red-500 focus:ring-red-200': {{ $errors->has($name) ? 'true' : 'false' }},
        }"
    >

    {{-- Error Message --}}
    @error($name)
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>