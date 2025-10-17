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
                }
            }" class="space-y-4">

                {{-- TOP CONTROLS AND BUTTONS (Unchanged) --}}
                <div class="flex justify-between items-center mb-4">
                    <h1 class="text-2xl font-bold text-gray-800">Orders List</h1>
                    
                    <div class="space-x-2 flex items-center">
                        <a href="{{ route('orders.export') }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest bg-green-600 hover:bg-green-700 transition duration-150 ease-in-out shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Convert to Excel
                        </a>

                        <a href="{{ route('orders.create') }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest bg-blue-600 hover:bg-blue-700 transition duration-150 ease-in-out shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Create New Order
                        </a>

                        {{-- Toggle Button for Mass Update --}}
                        <button type="button" @click="isEditing = !isEditing"
                            x-bind:class="isEditing ? 'bg-red-500 hover:bg-red-600 focus:ring-red-500' : 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500'"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-150 shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2">
                            <span x-text="isEditing ? 'Exit Mass Edit' : 'Toggle Mass Edit'"></span>
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
                <form action="{{ route('orders.mass-update') }}" method="POST">
                    @csrf
                    
                    <div class="mt-4 shadow-lg rounded-lg overflow-hidden overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-indigo-50 sticky top-0">
                                <tr>
                                    <th class="px-3 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-10">NO</th>
                                    
                                    {{-- NEW EDITABLE FIELDS ADDED/REPLACED --}}
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-40">ORDER NO</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-48">CLIENT</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-28">D-CODE</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-28">ORDER DATE</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-48">PROJECT NAME</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-36">PO NO.</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-28">PO DATE</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider min-w-32">AMOUNT</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider min-w-20">CUR</th>

                                    {{-- STATUS FIELDS (Static) --}}
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider min-w-32">OUTGOING STATUS</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider min-w-32">INCOMING STATUS</th>
                                    <th class="relative px-6 py-3 min-w-32">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                    $startNumber = 1;
                                    if ($isPaginator) {
                                        $startNumber = ($orders->currentPage() - 1) * $orders->perPage() + 1;
                                    }
                                @endphp
                                @foreach ($orders as $index => $order)
                                    <tr class="hover:bg-gray-50">
                                        {{-- NO --}}
                                        <td class="px-3 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            {{ $startNumber + $index }}</td>
                                        
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
                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-right font-bold text-green-600">
                                            <span x-show="!isEditing">{{ $order->formatted_amount ?? '0 IDR' }}</span>
                                            <input x-show="isEditing" type="text"
                                                name="orders[{{ $order->id }}][amount]"
                                                value="{{ old('orders.' . $order->id . '.amount', $order->amount) }}"
                                                x-bind:class="isEditing ? 'bg-yellow-100 border-2 border-yellow-500 focus:border-yellow-600 focus:ring-yellow-400' : 'border-gray-300 focus:border-indigo-300 focus:ring-indigo-200'"
                                                class="w-full text-sm rounded-md shadow-sm p-1 transition duration-150 focus:ring focus:ring-opacity-50 text-right"
                                                placeholder="Amount" :required="isEditing">
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

                                        {{-- 11. OUTGOING STATUS (Static) --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-xs">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if ($order->outgoing_status == 'COMPLETED') bg-green-100 text-green-800 @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ $order->outgoing_status ?? 'PENDING' }}
                                            </span>
                                        </td>
                                        
                                        {{-- 12. INCOMING STATUS (Static) --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-xs">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if ($order->incoming_status == 'PAID') bg-blue-100 text-blue-800 @else bg-red-100 text-red-800 @endif">
                                                {{ $order->incoming_status ?? 'N/A' }}
                                            </span>
                                        </td>

                                        {{-- 13. ACTIONS (Show and Delete) --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('orders.show', $order) }}"
                                                    class="text-indigo-600 hover:text-indigo-900 transition duration-150 ease-in-out font-semibold">
                                                    Show
                                                </a>

                                                <span x-show="!isEditing" class="text-gray-300">|</span>

                                                {{-- Button to trigger the separate, non-nested delete form --}}
                                                <button type="button" x-show="!isEditing"
                                                    @click="if (confirmDelete('{{ $order->ord_number }}')) { $refs.deleteForm_{{ $order->id }}.submit() }"
                                                    class="text-red-600 hover:text-red-900 transition duration-150 ease-in-out font-semibold">
                                                    Delete
                                                </button>

                                                <span x-show="isEditing" class="text-gray-400 font-medium">Mass Edit</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Save Button (Inside the mass update form) --}}
                    <div x-show="isEditing" class="mt-4 flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Save All Edited Orders
                        </button>
                    </div>
                </form>
                {{-- *** END MASS UPDATE FORM *** --}}
                
                
                {{-- *** INDIVIDUAL DELETE FORMS (OUTSIDE THE MASS UPDATE FORM) *** --}}
                @foreach ($orders as $order)
                    <form x-ref="deleteForm_{{ $order->id }}" action="{{ route('orders.destroy', $order) }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach
                
                <div class="mt-4">
                    {{ $orders->links() }}
                </div>
            </div> {{-- End Global Alpine Scope --}}
        </div>
    </div>
</x-app-layout>