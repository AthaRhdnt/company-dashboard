<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemSpec; // <--- FIX 1: ADD MISSING USE STATEMENT
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource. (Index)
     */
    public function index()
    {
        // Load the item specs count for the index view
        $items = Item::withCount('itemSpecs')->get();
        return view('pages.master.items.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource. (Create)
     */
    public function create()
    {
        // The form will handle Item + multiple ItemSpecs
        return view('pages.master.items.create');
    }

    /**
     * Store a newly created resource in storage. (Store)
     */
    public function store(Request $request)
    {
        // 1. Validate Item and Specs data
        $validatedData = $request->validate([
            'item_name' => 'required|string|max:255',
            'item_price' => 'required|numeric|min:0',
            // Specs data is now nested
            'specs' => 'nullable|array',
            // FIX 2: Relaxed validation to allow 'specs' to be null/empty array 
            // but require description if 'specs' is present
            'specs.*.item_description' => 'nullable|string|max:255', 
        ]);

        try {
            DB::beginTransaction();

            // 2. Create the main Item
            $item = Item::create([
                'item_name' => $validatedData['item_name'],
                'item_price' => $validatedData['item_price'],
            ]);

            // 3. Create the associated ItemSpecs
            if (isset($validatedData['specs'])) {
                $specsToCreate = [];
                foreach ($validatedData['specs'] as $spec) {
                    // Only create if description is not empty
                    if (!empty($spec['item_description'])) {
                        $specsToCreate[] = [
                            'item_id' => $item->id,
                            'item_description' => $spec['item_description'],
                            // Add timestamps for mass insertion
                            'created_at' => now(), 
                            'updated_at' => now(),
                        ];
                    }
                }
                if (!empty($specsToCreate)) {
                    // This line required the ItemSpec model import
                    ItemSpec::insert($specsToCreate); 
                }
            }

            DB::commit();
            return redirect()->route('items.index')->with('success', 'Item and specifications created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error for debugging
            \Log::error("ItemController@store failed: " . $e->getMessage()); 
            return back()->with('error', 'Failed to create item: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource. (Show)
     */
    public function show(Item $item)
    {
        // Eager load item specs for display
        $item->load('itemSpecs');
        $itemSpecs = $item->itemSpecs()->paginate(5); // You might not need pagination here
        
        // Pass $item which now contains all its specs
        return view('pages.master.items.show', compact('item', 'itemSpecs'));
    }

    /**
     * Show the form for editing the specified resource. (Edit)
     */
    public function edit(Item $item)
    {
        // Eager load specs for the edit form
        $item->load('itemSpecs');
        return view('pages.master.items.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage. (Update)
     */
    public function update(Request $request, Item $item)
    {
        // 1. Validate the updated data
        $validatedData = $request->validate([
            'item_name' => 'required|string|max:255',
            'item_price' => 'required|numeric|min:0',
            
            // For existing specs
            'existing_specs' => 'nullable|array',
            'existing_specs.*.id' => 'required|exists:item_specs,id',
            'existing_specs.*.item_description' => 'nullable|string|max:255', // Made nullable for safety
            
            // For new specs
            'new_specs' => 'nullable|array',
            'new_specs.*.item_description' => 'required_with:new_specs|string|max:255',
        ]);
        
        try {
            DB::beginTransaction();

            // 2. Update the main Item
            $item->update([
                'item_name' => $validatedData['item_name'],
                'item_price' => $validatedData['item_price'],
            ]);

            $existingSpecIds = [];

            // 3. Update Existing Specs
            if (isset($validatedData['existing_specs'])) {
                foreach ($validatedData['existing_specs'] as $specData) {
                    // Use findOrFail if you want a cleaner error, but find is safer here
                    $spec = $item->itemSpecs()->find($specData['id']); 
                    
                    if ($spec) {
                        // 3a. Update the description
                        // NOTE: The description is nullable in validation, so we can update it even if empty/null
                        $spec->update([
                            'item_description' => $specData['item_description'] ?? null
                        ]);
                        
                        // 3b. CRUCIAL FIX: ALWAYS add the ID to the list
                        // This marks the spec as "STILL EXISTS ON THE FORM".
                        $existingSpecIds[] = $spec->id; 
                    }
                }
            }
            
            // 4. Delete Specs that were removed in the form (syncing)
            // Get all current spec IDs and find the ones missing from the submitted list
            $item->itemSpecs()->whereNotIn('id', $existingSpecIds)->delete();
            
            // 5. Create New Specs
            if (isset($validatedData['new_specs'])) {
                $newSpecs = [];
                foreach ($validatedData['new_specs'] as $specData) {
                    if (!empty($specData['item_description'])) {
                         $newSpecs[] = [
                            'item_id' => $item->id,
                            'item_description' => $specData['item_description'],
                            'created_at' => now(), 
                            'updated_at' => now(),
                        ];
                    }
                }
                if (!empty($newSpecs)) {
                    // This line required the ItemSpec model import
                    ItemSpec::insert($newSpecs);
                }
            }

            DB::commit();
            return redirect()->route('items.index')->with('success', 'Item and specifications updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error for debugging
            \Log::error("ItemController@update failed: " . $e->getMessage()); 
            return back()->with('error', 'Failed to update item: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage. (Destroy)
     */
    public function destroy(Item $item)
    {
        try {
            // The static::deleting boot method in the Item model handles deleting specs
            $item->delete();
            return redirect()->route('items.index')->with('success', 'Item deleted successfully.');
        } catch (\Exception $e) {
            // This error handling is mostly for foreign key constraints if the item is linked elsewhere
            return redirect()->route('items.index')->with('error', 'Cannot delete Item, it may be linked to other records (e.g., invoices).');
        }
    }

    public function quickStore(Request $request)
    {
        // 1. Validate the minimal input data
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        // 2. Create the new Item
        $item = Item::create([
            'item_name' => $request->name,
            'item_price' => $request->price,
            // Add other required fields if necessary (e.g., user_id)
        ]);

        // 3. Return the new item data as JSON
        // The front-end JS will use this data to update the dropdown without a page refresh.
        return response()->json([
            'success' => true,
            'item' => $item,
            'message' => 'Item created successfully.',
        ]);
    }
}