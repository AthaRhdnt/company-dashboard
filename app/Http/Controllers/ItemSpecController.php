<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemSpec;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemSpecController extends Controller
{
    // The methods below are now generally unnecessary for your goal, but left for context.
    /*
    public function index() { ... }
    public function create(Request $request) { ... }
    public function show(ItemSpec $itemSpec) { ... }
    public function edit(ItemSpec $itemSpec) { ... }
    public function update(Request $request, Item $item) { ... } // This method is for bulk form updates, not inline.
    */

    /**
     * Store a newly created resource (single spec) via AJAX from the Item Show page.
     * This replaces the bulk quickStore you had previously.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'item_id' => 'required|exists:items,id',
            'item_description' => 'required|string|max:500', // Assuming max 500 chars
        ]);

        $spec = ItemSpec::create($validatedData);
        
        // Return the created spec as JSON for Alpine to add to the list
        return response()->json([
            'success' => true,
            'spec' => ['id' => $spec->id, 'item_description' => $spec->item_description],
            'message' => 'Specification created successfully.',
        ], 201); // 201 Created
    }

    /**
     * Update the specified resource (single spec) via AJAX (PUT).
     * This is the method the inline editing will hit.
     */
    public function updateInline(Request $request, ItemSpec $itemSpec)
    {
        $validatedData = $request->validate([
            'item_description' => 'required|string|max:500',
        ]);
        
        $itemSpec->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Specification updated successfully.',
            // Return the updated description in case of server-side formatting
            'item_description' => $itemSpec->item_description,
        ]);
    }

    /**
     * Remove the specified resource from storage via AJAX (DELETE).
     */
    public function destroy(ItemSpec $itemSpec)
    {
        $itemSpec->delete();

        return response()->json([
            'success' => true,
            'message' => 'Specification deleted successfully.',
        ]);
    }

    public function quickStore(Request $request)
    {
        // 1. Validate the minimal input data
        $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            // Validate each description in the array
            'descriptions' => ['nullable', 'array'],
            'descriptions.*' => ['nullable', 'string', 'max:500'], 
        ]);

        $createdSpecs = [];
        $itemId = $request->item_id;

        // 2. Loop through all descriptions and create specs
        foreach ($request->descriptions as $description) {
            if (!empty($description)) {
                $spec = ItemSpec::create([
                    'item_id' => $itemId,
                    'item_description' => $description,
                ]);
                $createdSpecs[] = $spec;
            }
        }

        // **IMPORTANT:** Add a check if any specs were actually created
        if (empty($createdSpecs)) {
            return response()->json([
                'success' => false,
                'message' => 'Error: You must provide at least one specification detail.',
            ], 422); // Use HTTP 422 for Unprocessable Entity
        }

        // 3. Return the new specs data as JSON
        return response()->json([
            'success' => true,
            'specs' => $createdSpecs, // Changed from 'spec' to 'specs'
            'message' => count($createdSpecs) . ' specifications created successfully.',
        ]);
    }
}