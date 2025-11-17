<x-app-layout>
    {{-- ITEM DETAILS SECTION --}}
    <x-pages.show resource="items" :item="$item" :details="[
        'Item Name' => $item->item_name,
        'Item Price' => $item->item_price,
    ]" />

    {{-- Horizontal Separator --}}
    <hr class="my-6">
    @php
        $isPaginator = $itemSpecs instanceof \Illuminate\Contracts\Pagination\Paginator;
    @endphp

    <div class="p-6">
        <div class="bg-white p-6 rounded-lg shadow-md">
            {{-- ITEM SPECS EDITABLE TABLE SECTION (New Content) --}}
            <h2 class="text-xl font-bold mb-4">Item Specifications</h2>

            <div x-data="{
                // Initial data for the table, including the current description
                specs: {{ json_encode($itemSpecs->map(fn($spec) => ['id' => $spec->id, 'item_description' => $spec->item_description])->toArray()) }},
            
                // Storage for the original data to check for changes
                originalSpecs: JSON.parse(JSON.stringify({{ $itemSpecs->map(fn($spec) => ['id' => $spec->id, 'item_description' => $spec->item_description])->toJson() }})),
            
                isEditing: false,
                newSpecDescription: '', // For the new spec input field
                csrfToken: '{{ csrf_token() }}',
                itemId: {{ $item->id }},
            
                // Flag to disable buttons during save operation
                isSaving: false,
            
                // --- Core Functions ---
            
                // Function to perform a single PUT update, only called if data changed
                async _performUpdate(spec) {
                    if (!spec.item_description) {
                        throw new Error('Description cannot be empty for spec ID: ' + spec.id);
                    }
            
                    const response = await fetch('/item-specs/inline/' + spec.id, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken
                        },
                        body: JSON.stringify({ item_description: spec.item_description })
                    });
            
                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error('Update failed for spec ' + spec.id + ': ' + (errorData.message || 'Server error.'));
                    }
                    return response.json();
                },
            
                // New function to handle saving all edited specifications
                async saveAllSpecs() {
                    this.isSaving = true;
                    let updatesCount = 0;
                    let errors = [];
            
                    // 1. Check existing specs and send PUT request if changed
                    for (let i = 0; i < this.specs.length; i++) {
                        const currentSpec = this.specs[i];
                        const originalSpec = this.originalSpecs.find(s => s.id === currentSpec.id);
            
                        // Check if the description has actually changed
                        if (originalSpec && originalSpec.item_description !== currentSpec.item_description) {
                            try {
                                await this._performUpdate(currentSpec);
                                // Update the original data so it's not saved again if saved multiple times
                                originalSpec.item_description = currentSpec.item_description;
                                updatesCount++;
                            } catch (error) {
                                errors.push(error.message);
                            }
                        }
                    }
            
                    this.isSaving = false;
            
                    if (errors.length > 0) {
                        alert('Finished saving with errors:\n' + errors.join('\n'));
                    } else if (updatesCount > 0) {
                        alert(updatesCount + ' specification(s) saved successfully.');
                    } else {
                        alert('No changes detected for existing specifications.');
                    }
                },
            
                // Function to create a single spec
                async createSpec() {
                    if (!this.newSpecDescription) {
                        alert('Please enter a description for the new spec.');
                        return;
                    }
            
                    try {
                        const response = await fetch('/item-specs', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify({
                                item_id: this.itemId,
                                item_description: this.newSpecDescription
                            })
                        });
            
                        if (!response.ok) throw new Error('Creation failed');
            
                        const data = await response.json();
            
                        // Add the new spec to both Alpine arrays
                        this.specs.push(data.spec);
                        this.originalSpecs.push(JSON.parse(JSON.stringify(data.spec))); // Deep copy for original
                        this.newSpecDescription = ''; // Clear the input
            
                        console.log('Spec created successfully:', data.spec.id);
            
                    } catch (error) {
                        console.error('Error creating spec:', error);
                        alert('Failed to create specification.');
                    }
                },
            
                // Function to delete a single spec
                async deleteSpec(id) {
                    if (!confirm('Are you sure you want to delete this specification?')) return;
            
                    try {
                        const response = await fetch('/item-specs/' + id, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken
                            }
                        });
            
                        if (!response.ok) throw new Error('Deletion failed');
            
                        // Remove the spec from both Alpine arrays
                        this.specs = this.specs.filter(s => s.id !== id);
                        this.originalSpecs = this.originalSpecs.filter(s => s.id !== id);
            
                        console.log('Spec deleted successfully:', id);
                    } catch (error) {
                        console.error('Error deleting spec:', error);
                        alert('Failed to delete specification.');
                    }
                },
            
                // Function to revert all unsaved changes when cancelling or exiting edit mode
                revertChanges() {
                    if (confirm('Are you sure you want to discard all unsaved changes?')) {
                        // Reset specs to the state of originalSpecs
                        this.specs = JSON.parse(JSON.stringify(this.originalSpecs));
                        this.newSpecDescription = ''; // Also clear the new spec input
                        this.isEditing = false;
                    }
                }
            }" class="p-4 bg-white shadow-lg rounded-lg">

                <div class="flex justify-between items-center mb-4 border-b pb-3">
                    <h3 class="text-lg font-semibold">Specifications List</h3>
                    <div class="space-x-3">

                        {{-- Global Save Button (Only in Edit Mode) --}}
                        <button type="button" x-show="isEditing" @click="saveAllSpecs()" :disabled="isSaving"
                            class="px-3 py-1 text-xs font-medium rounded-md shadow-sm transition bg-green-500 text-white hover:bg-green-600 disabled:opacity-50">
                            <span x-text="isSaving ? 'Saving...' : 'Save All Changes'"></span>
                        </button>

                        {{-- Cancel Button (Only in Edit Mode) --}}
                        <button type="button" x-show="isEditing" @click="revertChanges()" :disabled="isSaving"
                            class="px-3 py-1 text-xs font-medium rounded-md shadow-sm transition bg-red-500 text-white hover:bg-red-600 disabled:opacity-50">
                            Cancel
                        </button>

                        {{-- Toggle Edit Button (Reverts to Exit Edit Mode) --}}
                        <button type="button" @click="isEditing = !isEditing"
                            class="px-3 py-1 text-xs font-medium rounded-md shadow-sm transition"
                            x-bind:class="isEditing ? 'bg-gray-500 text-white hover:bg-gray-600' :
                                'bg-indigo-500 text-white hover:bg-indigo-600'">
                            <span x-text="isEditing ? 'Exit Edit Mode' : 'Enter Edit Mode'"></span>
                        </button>
                    </div>
                </div>

                @if ($itemSpecs->count() > 0 || ($itemSpecs->count() == 0 && request()->query('page')))
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        No.</th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Description</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        x-show="isEditing">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(spec, index) in specs" :key="spec.id">
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500"
                                            x-text="index + 1"></td>

                                        {{-- Description Cell (Toggled) --}}
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            {{-- Read-only display --}}
                                            <span x-text="spec.item_description" x-show="!isEditing"></span>

                                            {{-- Editable Input --}}
                                            <input type="text" x-model="spec.item_description" x-show="isEditing"
                                                class="w-full border rounded-md p-1 transition duration-150 focus:border-indigo-500">
                                        </td>

                                        {{-- Actions Cell (Only visible in Edit Mode) --}}
                                        <td class="px-4 py-3 whitespace-nowrap text-center text-sm font-medium"
                                            x-show="isEditing">
                                            <div class="flex justify-center space-x-3">
                                                {{-- Delete Button (Triggers DELETE request) --}}
                                                <button type="button" @click="deleteSpec(spec.id)"
                                                    class="text-red-600 hover:text-red-900">Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- New Spec Input (Only visible in Edit Mode) --}}
                    <div class="mt-4 p-3 border-t" x-show="isEditing">
                        <div class="flex space-x-3 items-center">
                            <input type="text" x-model="newSpecDescription" placeholder="Add a new specification..."
                                @keydown.enter.prevent="createSpec()"
                                class="flex-grow border rounded-md p-2 focus:border-indigo-500">

                            {{-- Add New Spec Button (Triggers POST request) --}}
                            <button type="button" @click="createSpec()"
                                class="px-3 py-2 text-sm font-medium rounded-md shadow-sm bg-green-500 text-white hover:bg-green-600 transition">
                                Add Spec
                            </button>
                        </div>
                    </div>

                    @if ($isPaginator)
                        <div class="mt-4">
                            {{ $itemSpecs->links() }}
                        </div>
                    @endif
                @else
                    {{-- No Specs Display --}}
                    <div class="text-center p-6 border-2 border-dashed border-gray-300 rounded-lg">
                        <p class="text-gray-600 mb-3">No specifications recorded for this item.</p>
                        <div x-show="isEditing">
                            <p class="text-sm text-gray-500 mb-2">Use the "Add Spec" field above to start.</p>
                        </div>
                        <div x-show="!isEditing">
                            <button type="button" @click="isEditing = true"
                                class="text-indigo-600 font-semibold hover:text-indigo-800">
                                Click here to start adding specifications.
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
