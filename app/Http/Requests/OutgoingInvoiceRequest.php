<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OutgoingInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Base rules for the main table columns
        $rules = [
            'inv_number'      => 'required|string|max:255|unique:outgoing_invoices,inv_number,' . ($this->route('outgoing_invoice')->id ?? 'NULL') . ',id',
            'inv_date'        => 'required|date',
            'due_date'        => 'required|date|after_or_equal:inv_date',
            'fp_number'       => 'nullable|string|max:255',
            'income_date'     => 'nullable|date',
            'cur'             => 'required|string|max:10',
            'amount'          => 'required|numeric|min:0',
            'po_number'       => 'nullable|string|max:255',
            'order_id'        => 'required|exists:orders,id',
            'client_id'       => 'required|exists:clients,id', // Assuming clients table exists
            'department_id'   => 'required|exists:departments,id', // Assuming departments table exists
            'do_number'       => 'nullable|string|max:255', // The new column
            // Rules for taxes (array of tax IDs)
            'tax_ids'         => 'nullable|array',
            'tax_ids.*'       => 'exists:taxes,id',
            // Rules for invoice items (nested array)
            'items'           => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:invoice_item_spec,item_id', // Assuming you select an item from a list
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.subtotal' => 'required|numeric|min:0',
        ];

        return $rules;
    }
}
