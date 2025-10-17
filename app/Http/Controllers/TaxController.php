<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $taxes = Tax::orderBy('tax_percentage', 'asc')->paginate(10);
        return view('pages.master.taxes.index', compact('taxes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.master.taxes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tax_name' => 'required|string|max:255|unique:taxes,tax_name',
            // Tax percentage is stored as a DECIMAL in the database
            'tax_percentage' => 'required|numeric|between:0.01,99.99', 
        ]);

        Tax::create($validated);

        return redirect()->route('taxes.index')->with('success', 'Tax added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tax $tax)
    {
        return view('pages.master.taxes.edit', compact('tax'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tax $tax)
    {
        $validated = $request->validate([
            // Exclude the current tax record from the unique check
            'tax_name' => 'required|string|max:255|unique:taxes,tax_name,' . $tax->id,
            'tax_percentage' => 'required|numeric|between:0.01,99.99',
        ]);

        $tax->update($validated);

        return redirect()->route('taxes.index')->with('success', 'Tax updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tax $tax)
    {
        try {
            $tax->delete();
            return redirect()->route('taxes.index')->with('success', 'Tax deleted successfully.');
        } catch (\Exception $e) {
            // Check for foreign key constraint violation (used in invoices)
            return redirect()->route('taxes.index')->with('error', 'Cannot delete tax, it is currently in use by one or more invoices.');
        }
    }
}
