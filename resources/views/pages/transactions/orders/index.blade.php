<x-app-layout>
    @php
        $isPaginator = $orders instanceof \Illuminate\Contracts\Pagination\Paginator;
        // Assume these variables are passed from the controller, setting a fallback for context
        $clients = $clients ?? collect(); 
        $departments = $departments ?? collect(); 
        $currencies = $currencies ?? ['IDR', 'USD', 'SGD', 'EUR', 'JPY'];
    @endphp
    <div class="p-6">
        <div class="bg-white p-6 rounded-lg shadow-md">
            
            {{-- GLOBAL ALPINE SCOPE --}}
            <div x-data="{
                isEditing: {{ count($errors) > 0 ? 'true' : 'false' }},
                confirmDelete: (orderNumber) => {
                    return confirm(`Are you sure you want to delete MCN Order ${orderNumber} and ALL associated documents (Invoices, Items, PO)? This action cannot be undone.`);
                },
                confirmCancel: (orderNumber) => {
                    return confirm(`Are you sure you want to cancel Order ${orderNumber}? This will set all amounts to 0.`);
                }
            }" class="space-y-4">

                {{-- TOP CONTROLS AND BUTTONS (Unchanged) --}}
                <div class="sticky top-0 z-10 bg-white/90 backdrop-blur-md p-4 -mt-4 mb-4 rounded-xl border border-gray-200 shadow-sm transition-all duration-300 ease-in-out flex flex-col md:flex-row justify-between items-start md:items-center gap-4 md:gap-0">
                    <h1 class="text-3xl font-bold text-gray-900 mb-3 md:mb-0">Orders List</h1>
                    
                    <div class="space-x-2 flex items-center">
                        <a x-show="!isEditing" href="{{ route('orders.export') }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest bg-green-600 hover:bg-green-700 transition duration-150 ease-in-out shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Convert to Excel
                        </a>

                        <a x-show="!isEditing" href="{{ route('orders.create') }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest bg-blue-600 hover:bg-blue-700 transition duration-150 ease-in-out shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Create New Order
                        </a>

                        <button x-show="isEditing" type="button" @click="$refs.massUpdateForm.submit()"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 0 1 1-1h11.586a1 1 0 0 1 .707.293l2.414 2.414a1 1 0 0 1 .293.707V19a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5Z"/>
                                <path stroke="currentColor" stroke-linejoin="round" stroke-width="2" d="M8 4h8v4H8V4Zm7 10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                            Save
                        </button>

                        {{-- Toggle Button for Mass Update --}}
                        <button type="button" @click="isEditing = !isEditing"
                            x-bind:class="isEditing ? 'bg-red-500 hover:bg-red-600 focus:ring-red-500' : 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500'"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-150 shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2">
                            <span x-text="isEditing ? 'Exit' : 'Edit Mode'"></span>
                        </button>
                    </div>
                </div>

                {{-- Flash Messages (Unchanged) --}}
                @if (session('success'))
                    <div x-data="{ showMessage: true }" x-show="showMessage" x-transition.opacity
                        class="p-4 bg-green-100 text-green-700 rounded-md border border-green-200 flex justify-between items-start">
                        <span>{{ session('success') }}</span>
                        <button type="button" @click="showMessage = false"
                            class="ml-4 -mt-1 p-1 rounded-full text-green-700 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-600/50">
                            {{-- SVG Cross Icon --}}
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif
                @if (session('error'))
                    <div x-data="{ showMessage: true }" x-show="showMessage" x-transition.opacity
                        class="p-4 bg-red-100 text-red-700 rounded-md border border-red-200 flex justify-between items-start">
                        <span>{{ session('error') }}</span>
                        <button type="button" @click="showMessage = false"
                            class="ml-4 -mt-1 p-1 rounded-full text-red-700 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-600/50">
                            {{-- SVG Cross Icon --}}
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif

                {{-- *** START MASS UPDATE FORM *** --}}
                <form x-ref="massUpdateForm" id="massUpdateForm" action="{{ route('orders.mass-update') }}" method="POST">
                    @csrf
                    
                    <div class="mt-4 shadow-lg rounded-lg overflow-hidden overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-indigo-50 sticky top-0">
<tr class="border-b border-gray-200 bg-gray-50/50">
    <th class="px-3 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wide w-10">NO</th>
    
    <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide min-w-[10rem]">ORDER NO</th>
    <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide min-w-[12rem]">CLIENT</th>
    <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide min-w-[7rem]">D-CODE</th>
    <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide min-w-[8rem]">ORDER DATE</th>
    <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide min-w-[12rem]">PROJECT NAME</th>
    <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide min-w-[9rem]">PO NO.</th>
    <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide min-w-[8rem]">PO DATE</th>
    <th class="px-2 py-2 text-right text-xs font-semibold text-gray-700 uppercase tracking-wide min-w-[8rem]">AMOUNT</th>
    <th class="px-2 py-2 text-center text-xs font-semibold text-gray-700 uppercase tracking-wide min-w-[5rem]">CUR</th>
    <th class="px-2 py-2 text-center text-xs font-semibold text-gray-700 uppercase tracking-wide min-w-[8rem]">STATUS</th>
    <th class="px-2 py-2 text-center text-xs font-semibold text-gray-700 uppercase tracking-wide min-w-[8rem]">REMARKS</th>
    <th class="px-2 py-2 text-center text-xs font-semibold text-gray-700 uppercase tracking-wide min-w-[8rem]">ACTIONS</th>
</tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                    $startNumber = 1;
                                    if ($isPaginator) {
                                        $startNumber = ($orders->currentPage() - 1) * $orders->perPage() + 1;
                                    }
                                @endphp
                                @forelse ($orders as $index => $order)
                                    <tr class="hover:bg-gray-200">
                                        {{-- NO --}}
                                        <td class="px-3 py-3 whitespace-nowrap text-center text-sm text-gray-500">
                                            {{ $startNumber + $loop->index }}
                                        
                                        {{-- Hidden Input for Order ID (REQUIRED) --}}
                                        <input x-show="isEditing" type="hidden" name="orders[{{ $order->id }}][id]"
                                            value="{{ $order->id }}">

                                        {{-- **UPDATED** 2. ORDER NO (Editable Text) --}}
                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-indigo-700 font-medium">
                                            <span x-show="!isEditing">{{ $order->ord_number ?? 'N/A' }}</span>
                                            <input x-show="isEditing" type="text"
                                                name="orders[{{ $order->id }}][ord_number]"
                                                value="{{ old('orders.' . $order->id . '.ord_number', $order->ord_number) }}"
                                                x-bind:class="isEditing ? 'bg-yellow-100 border-2 border-yellow-500 focus:border-yellow-600 focus:ring-yellow-400' : 'border-gray-300 focus:border-indigo-300 focus:ring-indigo-200'"
                                                class="w-full text-sm rounded-md shadow-sm p-1 transition duration-150 focus:ring focus:ring-opacity-50"
                                                placeholder="Order Number" :required="isEditing">
                                            @error('orders.' . $order->id . '.ord_number')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </td>
                                        
                                        {{-- 3. CLIENT (Editable Select) --}}
                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-900 font-medium">
                                            <span x-show="!isEditing">{{ $order->client->client_name ?? 'N/A' }}</span>
                                            <select x-show="isEditing" 
                                                name="orders[{{ $order->id }}][client_id]" 
                                                x-bind:class="isEditing ? 'bg-yellow-100 border-2 border-yellow-500 focus:border-yellow-600 focus:ring-yellow-400' : 'border-gray-300 focus:border-indigo-300 focus:ring-indigo-200'"
                                                class="w-full text-sm rounded-md shadow-sm p-1 transition duration-150 focus:ring focus:ring-opacity-50"
                                                :required="isEditing">
                                                <option value="">Select Client</option>
                                                @foreach ($clients as $client)
                                                    <option value="{{ $client->id }}" 
                                                        @selected(old('orders.' . $order->id . '.client_id', $order->client_id) == $client->id)>
                                                        {{ $client->client_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('orders.' . $order->id . '.client_id')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </td>

                                        {{-- 4. DEPARTMENT (NEW: Editable Select) --}}
                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-700">
                                            <span x-show="!isEditing">{{ $order->department->department_code ?? 'N/A' }}</span>
                                            <select x-show="isEditing" 
                                                name="orders[{{ $order->id }}][department_id]" 
                                                x-bind:class="isEditing ? 'bg-yellow-100 border-2 border-yellow-500 focus:border-yellow-600 focus:ring-yellow-400' : 'border-gray-300 focus:border-indigo-300 focus:ring-indigo-200'"
                                                class="w-full text-sm rounded-md shadow-sm p-1 transition duration-150 focus:ring focus:ring-opacity-50"
                                                :required="isEditing">
                                                <option value="">Select Dept.</option>
                                                @foreach ($departments as $department)
                                                    <option value="{{ $department->id }}" 
                                                        @selected(old('orders.' . $order->id . '.department_id', $order->department_id) == $department->id)>
                                                        {{ $department->department_code }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('orders.' . $order->id . '.department_id')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </td>
                                        
                                        {{-- 5. ORDER DATE (Existing, Consolidated Styling) --}}
                                        @php
                                            $ordDateValue = $order->ord_date
                                                ? \Carbon\Carbon::parse($order->ord_date)->format('Y-m-d')
                                                : null;
                                        @endphp
                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500">
                                            <span x-show="!isEditing">{{ $order->ord_date ? \Carbon\Carbon::parse($order->ord_date)->format('d M Y') : 'N/A' }}</span>
                                            <input x-show="isEditing" type="date"
                                                name="orders[{{ $order->id }}][ord_date]"
                                                value="{{ old('orders.' . $order->id . '.ord_date', $ordDateValue) }}"
                                                x-bind:class="isEditing ? 'bg-yellow-100 border-2 border-yellow-500 focus:border-yellow-600 focus:ring-yellow-400' : 'border-gray-300 focus:border-indigo-300 focus:ring-indigo-200'"
                                                class="w-full text-sm rounded-md shadow-sm p-1 transition duration-150 focus:ring focus:ring-opacity-50"
                                                :required="isEditing">
                                            @error('orders.' . $order->id . '.ord_date')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </td>
                                        
                                        {{-- 6. PROJECT NAME (Existing, Consolidated Styling) --}}
                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-700">
                                            <span x-show="!isEditing"
                                                class="truncate max-w-sm block">{{ $order->project_name ?? '-' }}</span>
                                            <input x-show="isEditing" type="text"
                                                name="orders[{{ $order->id }}][project_name]"
                                                value="{{ old('orders.' . $order->id . '.project_name', $order->project_name) }}"
                                                x-bind:class="isEditing ? 'bg-yellow-100 border-2 border-yellow-500 focus:border-yellow-600 focus:ring-yellow-400' : 'border-gray-300 focus:border-indigo-300 focus:ring-indigo-200'"
                                                class="w-full text-sm rounded-md shadow-sm p-1 transition duration-150 focus:ring focus:ring-opacity-50"
                                                placeholder="Project Name">
                                            @error('orders.' . $order->id . '.project_name')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </td>
                                        
                                        {{-- 7. P.O. NUMBER (NEW: Editable Text) --}}
                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-700">
                                            <span x-show="!isEditing">{{ $order->purchaseOrder->po_number ?? 'N/A' }}</span>
                                            <input x-show="isEditing" type="text"
                                                name="orders[{{ $order->id }}][po_number]"
                                                value="{{ old('orders.' . $order->id . '.po_number', $order->purchaseOrder->po_number ?? '') }}"
                                                x-bind:class="isEditing ? 'bg-yellow-100 border-2 border-yellow-500 focus:border-yellow-600 focus:ring-yellow-400' : 'border-gray-300 focus:border-indigo-300 focus:ring-indigo-200'"
                                                class="w-full text-sm rounded-md shadow-sm p-1 transition duration-150 focus:ring focus:ring-opacity-50"
                                                placeholder="PO Number" :required="isEditing">
                                            @error('orders.' . $order->id . '.po_number')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </td>

                                        {{-- 8. P.O. DATE (NEW: Editable Date) --}}
                                        @php
                                            $poDateValue = optional($order->purchaseOrder)->po_date
                                                ? \Carbon\Carbon::parse($order->purchaseOrder->po_date)->format('Y-m-d')
                                                : null;
                                        @endphp
                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-700">
                                            <span x-show="!isEditing">{{ $poDateValue ? \Carbon\Carbon::parse($poDateValue)->format('d M Y') : 'N/A' }}</span>
                                            <input x-show="isEditing" type="date"
                                                name="orders[{{ $order->id }}][po_date]"
                                                value="{{ old('orders.' . $order->id . '.po_date', $poDateValue) }}"
                                                x-bind:class="isEditing ? 'bg-yellow-100 border-2 border-yellow-500 focus:border-yellow-600 focus:ring-yellow-400' : 'border-gray-300 focus:border-indigo-300 focus:ring-indigo-200'"
                                                class="w-full text-sm rounded-md shadow-sm p-1 transition duration-150 focus:ring focus:ring-opacity-50"
                                                :required="isEditing">
                                            @error('orders.' . $order->id . '.po_date')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </td>

                                        {{-- 9. AMOUNT (NEW: Editable Number) --}}
                                        @php
                                            $amount = $order->amount;
                                            $amountColor = $amount > 0 ? 'text-green-600' : 'text-red-600';
                                        @endphp

                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-right font-bold">
                                            {{-- DISPLAY MODE --}}
                                            <span x-show="!isEditing" class="{{ $amountColor }}">
                                                {{ $order->formatted_amount ?? '0 IDR' }}
                                            </span>
                                            <input x-show="isEditing" type="text"
                                                name="orders[{{ $order->id }}][amount]"
                                                value="{{ old('orders.' . $order->id . '.amount', $amount) }}"
                                                x-bind:class="isEditing
                                                    ? 'bg-yellow-100 border-2 border-yellow-500 focus:border-yellow-600 focus:ring-yellow-400'
                                                    : 'border-gray-300 focus:border-indigo-300 focus:ring-indigo-200'
                                                "
                                                class="w-full text-sm rounded-md shadow-sm p-1 transition duration-150 focus:ring focus:ring-opacity-50 text-right"
                                                placeholder="Amount"
                                                :required="isEditing">

                                            @error('orders.' . $order->id . '.amount')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </td>

                                        {{-- 10. CURRENCY (NEW: Editable Select) --}}
                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-center text-gray-700">
                                            <span x-show="!isEditing">{{ $order->cur ?? 'N/A' }}</span>
                                            <select x-show="isEditing" 
                                                name="orders[{{ $order->id }}][cur]" 
                                                x-bind:class="isEditing ? 'bg-yellow-100 border-2 border-yellow-500 focus:border-yellow-600 focus:ring-yellow-400' : 'border-gray-300 focus:border-indigo-300 focus:ring-indigo-200'"
                                                class="w-full text-sm rounded-md shadow-sm p-1 transition duration-150 focus:ring focus:ring-opacity-50"
                                                :required="isEditing">
                                                @foreach ($currencies as $currency)
                                                    <option value="{{ $currency }}" 
                                                        @selected(old('orders.' . $order->id . '.cur', $order->cur) == $currency)>
                                                        {{ $currency }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('orders.' . $order->id . '.cur')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </td>

                                        {{-- 11. STATUS (Static) --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-xs">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if ($order->transaction_status === 'Completed') 
                                                    bg-green-100 text-green-800 
                                                @elseif ($order->transaction_status === 'Cancelled')
                                                    bg-red-100 text-red-800
                                                @else
                                                    bg-yellow-100 text-yellow-800 
                                                @endif">
                                                {{ $order->transaction_status }}
                                            </span>
                                        </td>

                                        {{-- 12. REMARKS --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-left text-xs">
                                            <span x-show="!isEditing"
                                                class="truncate max-w-sm block">{{ $order->remark ?? '-' }}</span>
                                            <input x-show="isEditing" type="text"
                                                name="orders[{{ $order->id }}][remark]"
                                                value="{{ old('orders.' . $order->id . '.remark', $order->remark) }}"
                                                x-bind:class="isEditing ? 'bg-yellow-100 border-2 border-yellow-500 focus:border-yellow-600 focus:ring-yellow-400' : 'border-gray-300 focus:border-indigo-300 focus:ring-indigo-200'"
                                                class="w-full text-sm rounded-md shadow-sm p-1 transition duration-150 focus:ring focus:ring-opacity-50"
                                                placeholder="Remark(s)">
                                            @error('orders.' . $order->id . '.remark')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </td>

                                        {{-- 13. ACTIONS (Show and Delete) --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('orders.show', $order) }}"
                                                    class="text-indigo-600 hover:text-indigo-900 transition duration-150 ease-in-out font-semibold">
                                                    Show
                                                </a>

                                                <span x-show="!isEditing" class="text-gray-300">|</span>

                                                {{-- If CANCELLED (amount == 0), disable cancel button --}}
                                                @if ($order->amount == 0 && Str::contains(strtolower($order->remark), 'cancelled'))
                                                    <span class="text-gray-400 font-semibold cursor-not-allowed">Cancel</span>
                                                @else
                                                    {{-- Active Cancel button --}}
                                                    <button type="button" x-show="!isEditing"
                                                        @click="if (confirmCancel('{{ $order->ord_number }}')) { $refs.deleteForm_{{ $order->id }}.submit() }"
                                                        class="text-red-600 hover:text-red-900 transition duration-150 ease-in-out font-semibold">
                                                        Cancel
                                                    </button>
                                                    <span x-show="isEditing" class="text-gray-400 font-medium">Mass Edit</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="px-4 py-6 text-center text-sm text-gray-500">
                                            No data available.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>
                {{-- *** END MASS UPDATE FORM *** --}}
                
                
                {{-- *** INDIVIDUAL DELETE FORMS (OUTSIDE THE MASS UPDATE FORM) *** --}}
                @foreach ($orders as $order)
                    <form x-ref="deleteForm_{{ $order->id }}" action="{{ route('orders.cancel', $order) }}" method="POST" class="hidden">
                        @csrf
                        @method('PATCH')
                    </form>
                @endforeach
                
                <div class="mt-4">
                    {{ $orders->links() }}
                </div>
            </div> {{-- End Global Alpine Scope --}}
        </div>
    </div>
</x-app-layout>