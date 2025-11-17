@php
    // Determine the array name dynamically:
    // If $spec exists (on the Edit page), use 'existing_specs' for the controller's update logic.
    // If $spec is null (on the Create page), use 'specs' for the controller's store logic.
    $arrayName = $spec ? 'existing_specs' : 'specs';
    
    // Construct the full field name
    $fieldName = "{$arrayName}[{$index}]";
    
    // Retrieve old data using the correct array name
    $inputValue = $spec 
        ? $spec->item_description 
        : old("{$arrayName}.{$index}.item_description", '');
@endphp

<div class="spec-row flex items-center space-x-2 mt-2 p-2 border border-gray-200 rounded-md">
    {{-- Hidden ID field for existing specs (only relevant on Edit) --}}
    @if($spec)
        <input type="hidden" name="{{ $fieldName }}[id]" value="{{ $spec->id }}"> 
    @endif
    
    <div class="flex-grow">
        <label for="{{ $fieldName }}_description" class="sr-only">Description</label>
        <x-text-input 
            type="text" 
            id="{{ $fieldName }}_description" 
            {{-- Name is now dynamic (specs[i][...] or existing_specs[i][...]) --}}
            name="{{ $fieldName }}[item_description]" 
            placeholder="Specification Description"
            value="{{ $inputValue }}"
            class="w-full"
            required
        />
    </div>
    
    {{-- Keep the remove button logic the same --}}
    @if($spec || $index > 0)
        <button type="button" class="remove-spec-btn text-red-500 hover:text-red-700 p-2">
            &times;
        </button>
    @endif
</div>