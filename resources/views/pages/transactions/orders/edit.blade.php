<x-app-layout>
    <x-pages.form resource="orders" action="update" :item="$order">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Core Order Details</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Order Number --}}
            <div>
                <label for="ord_number" class="block text-sm font-medium text-gray-700">Order Number:</label>
                <input type="text" id="ord_number" name="ord_number" value="{{ old('ord_number', $order->ord_number) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            
            {{-- Order Date --}}
            <div>
                <label for="ord_date" class="block text-sm font-medium text-gray-700">Order Date:</label>
                {{-- Ensure date format is correct for input type date --}}
                <input type="date" id="ord_date" name="ord_date" value="{{ old('ord_date', $order->ord_date) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            
            {{-- Project Name --}}
            <div>
                <label for="project_name" class="block text-sm font-medium text-gray-700">Project Name:</label>
                <input type="text" id="project_name" name="project_name" value="{{ old('project_name', $order->project_name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            {{-- Currency --}}
            <div>
                <label for="cur" class="block text-sm font-medium text-gray-700">Currency:</label>
                <select id="cur" name="cur" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="IDR" {{ old('cur', $order->cur) == 'IDR' ? 'selected' : '' }}>IDR</option>
                    <option value="USD" {{ old('cur', $order->cur) == 'USD' ? 'selected' : '' }}>USD</option>
                </select>
            </div>
            
            {{-- Amount (Revenue) --}}
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700">Total Revenue:</label>
                <input type="number" id="amount" name="amount" value="{{ old('amount', $order->amount) }}" step="0.01" required min="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            {{-- Client --}}
            <div>
                <label for="client_id" class="block text-sm font-medium text-gray-700">Client:</label>
                <select id="client_id" name="client_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select a Client</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id', $order->client_id) == $client->id ? 'selected' : '' }}>{{ $client->client_name }}</option>
                    @endforeach
                </select>
            </div>
            
            {{-- Department --}}
            <div>
                <label for="department_id" class="block text-sm font-medium text-gray-700">Department:</label>
                <select id="department_id" name="department_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select a Department</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" {{ old('department_id', $order->department_id) == $department->id ? 'selected' : '' }}>{{ $department->department_code }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <hr class="my-6">
        
        <h2 class="text-xl font-bold text-gray-800 mb-4">Customer Purchase Order</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- PO Number --}}
            <div>
                <label for="po_number" class="block text-sm font-medium text-gray-700">PO Number:</label>
                <input type="text" id="po_number" name="po_number" value="{{ old('po_number', $order->purchaseOrder->po_number ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            {{-- PO Date --}}
            <div>
                <label for="po_date" class="block text-sm font-medium text-gray-700">PO Date:</label>
                {{-- Use optional() for purchaseOrder as a safety net --}}
                <input type="date" id="po_date" name="po_date" value="{{ old('po_date', $order->purchaseOrder->po_date ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
        </div>
        
        <p class="mt-4 text-sm text-gray-500">Note: Financial terms (Profit Margin, Taxes) and Invoice Line Items must be updated in their respective dedicated views/controllers.</p>
    </x-pages.form>
</x-app-layout>