<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemSpec;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemSpecController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $itemSpecs = ItemSpec::with('item')->paginate(5);
        return view('pages.master.item-specs.index', compact('itemSpecs'));
        // dd($itemSpecs); 
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $items = Item::all();
        return view('pages.master.item-specs.create', compact('items'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation for each item in the array
        $validatedData = $request->validate([
            'item_specs' => 'required|array',
            'item_specs.*.item_description' => 'required|string|max:255',
            'item_specs.*.item_id' => 'required|exists:items,id',
        ]);

        // try {
        //     DB::beginTransaction();

            foreach ($validatedData['item_specs'] as $spec) {
                ItemSpec::create([
                    'item_description' => $spec['item_description'],
                    'item_id' => $spec['item_id'],
                ]);
            }

            return redirect()->route('item_specs.index')->with('success', 'Item spec created successfully.');

        //     DB::commit();

        //     return redirect()->route('item_specs.index')->with('success', 'Item specs created successfully.');
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     return back()->with('error', 'Failed to create item specs: ' . $e->getMessage())->withInput();
        // }
    }

    /**
     * Display the specified resource.
     */
    public function show(ItemSpec $itemSpec)
    {
        $itemSpec->load('item');
        return view('pages.master.item-specs.show', compact('itemSpec'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ItemSpec $itemSpec)
    {
        $items = Item::all();
        return view('pages.master.item-specs.edit', compact('itemSpec', 'items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ItemSpec $itemSpec)
    {
        $validatedData = $request->validate([
            'item_description' => 'required|string|max:255',
            'item_id' => 'required|exists:items,id',
        ]);

        $itemSpec->update($validatedData);

        return redirect()->route('item-specs.index')->with('success', 'Item spec updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ItemSpec $itemSpec)
    {
        try {
            $itemSpec->delete();
            return redirect()->route('item-specs.index')->with('success', 'Item spec deleted successfully.');
        } catch (\Exception $e) {
            // Provide a friendly error message if the spec is linked to an invoice item
            return redirect()->route('item-specs.index')->with('error', 'Cannot delete Item Spec, it is currently linked to one or more invoice line items.');
        }
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
