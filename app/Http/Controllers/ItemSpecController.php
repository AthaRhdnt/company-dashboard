<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemSpec;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemSpecController extends Controller
{
    /**
     * Store a newly created resource (single spec) via AJAX from the Item Show page.
     * This replaces the bulk quickStore you had previously.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'item_id' => 'required|exists:items,id',
            'item_description' => 'required|string|max:500',
        ]);

        $spec = ItemSpec::create($validatedData);
        
        // Return the created spec as JSON for Alpine to add to the list
        return response()->json([
            'success' => true,
            'spec' => ['id' => $spec->id, 'item_description' => $spec->item_description],
            'message' => 'Specification created successfully.',
        ], 201);
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
            ], 422);
        }

        // 3. Return the new specs data as JSON
        return response()->json([
            'success' => true,
            'specs' => $createdSpecs,
            'message' => count($createdSpecs) . ' specifications created successfully.',
        ]);
    }
}