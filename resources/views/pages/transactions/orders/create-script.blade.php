<script>
    // This defines the Alpine component logic in the global scope
    window.orderForm = (initialItems, initialTaxes) => ({
        // --- STATE VARIABLES ---
        itemSpecsMap: initialItems,
        orderItems: [], 
        totalPayment: 0,
        agreementPercentage: 4.0,
        incomingTaxes: initialTaxes.map(tax => ({
            ...tax,
            checked: false, // Initial state is unchecked
            percentage: parseFloat(tax.tax_percentage) || 0,
        })),
        isSubtotalOverridden: false,
        // Helper map for quick spec filtering (used in filterSpecs)
        
        // --- LIFECYCLE HOOK ---
        init() {
            // Load initial values from existing inputs (if any)
            const paymentInput = document.getElementById('total_customer_payment');
            const percentInput = document.getElementById('agreement_percentage');
            
            this.totalPayment = this.parseForCalculation(paymentInput.value || 0);
            this.agreementPercentage = parseFloat(percentInput.value || 4.0);

            // Set initial state for tax checkboxes if they were selected (e.g., from a form validation failure)
            document.querySelectorAll('input[name="incoming_tax_ids[]"]:checked').forEach(checkbox => {
                const taxId = parseInt(checkbox.value);
                const tax = this.incomingTaxes.find(t => t.id === taxId);
                if (tax) tax.checked = true;
            });
            
            // Add a starter item only if the page is empty
            if (this.orderItems.length === 0) {
                this.addItem();
            }
        },
        
        // --- FORMATTING HELPERS (Used by both inputs and display) ---
        formatForInput(value, isCurrency = false) {
            if (isNaN(value) || value === null) return isCurrency ? 'Rp0.00' : '0.00';
            const options = { minimumFractionDigits: 2, maximumFractionDigits: 2 };
            const formatter = new Intl.NumberFormat('en-US', options);
            let formatted = formatter.format(value);
            return isCurrency ? 'Rp' + formatted : formatted;
        },

        parseForCalculation(formattedValue) {
            const cleanString = String(formattedValue).replace(/Rp/g, '').replace(/,/g, '').trim();
            return parseFloat(cleanString) || 0;
        },

        // --- CORE CALCULATION (Computed Property) ---
        get finalVendorAmount() {
            // Recalculates automatically whenever totalPayment, agreementPercentage, or incomingTaxes.checked changes
            const totalPayment = this.totalPayment;
            const agreementRate = this.agreementPercentage / 100;
            let totalDeductionRate = agreementRate;

            this.incomingTaxes.forEach(tax => {
                if (tax.checked) {
                    totalDeductionRate += (tax.percentage / 100);
                }
            });

            let finalAmount = totalPayment * (1 - totalDeductionRate);
            return parseFloat(finalAmount.toFixed(2));
        },
        
        // --- ITEM ROW MANAGEMENT ---
        addItem() {
            this.orderItems.push({
                // Use a unique ID for Alpine's :key binding
                id: Date.now(), 
                item_id: '',
                quantity: 1,
                unit_price: 0.00,
                // Apply initial subtotal state based on global override flag
                subtotal: this.isSubtotalOverridden ? '0.00' : this.formatForInput(0.00, false),
                item_spec_ids: [],
                // Temporary store for specs associated with this item
                availableSpecs: [], 
            });
            this.$nextTick(() => {
                // Initialize Select2 on the newly added spec dropdown
                const newRow = document.querySelector(`#invoice-items-body tr:last-child`);
                if (newRow && typeof InitializeSelect2 === 'function') {
                    InitializeSelect2(newRow.querySelector('.spec-select'));
                }
            });
        },

        removeItem(id) {
            this.orderItems = this.orderItems.filter(item => item.id !== id);
        },

        updateItemState(item) {
            // Find item data
            const itemData = this.itemSpecsMap.find(data => data.id == item.item_id);
            const unitPrice = itemData ? (parseFloat(itemData.item_price) || 0) : 0;
            
            // 1. Update the unit price (read-only field)
            item.unit_price = unitPrice; 

            // 2. Update subtotal based on override state
            if (!this.isSubtotalOverridden) {
                const calculatedSubtotal = unitPrice * (item.quantity || 0);
                item.subtotal = this.formatForInput(calculatedSubtotal, false);
            }
            
            // 3. Update available specs
            item.availableSpecs = itemData && itemData.item_specs ? itemData.item_specs : [];
            
            // Note: finalVendorAmount is automatically recalculated because it depends on orderItems.subtotal
        },
        
        // --- UI & MODAL HELPERS ---
        toggleSubtotalOverride() {
            this.isSubtotalOverridden = !this.isSubtotalOverridden;
            if (!this.isSubtotalOverridden) {
                // If override is disabled, recalculate all subtotals
                this.orderItems.forEach(item => {
                    this.updateItemState(item); 
                });
            }
        },

        showItemModal() {
             document.getElementById('item-modal').style.display = 'flex';
        },
        hideItemModal() {
            document.getElementById('item-modal').style.display = 'none';
        },
        showSpecModal() {
            document.getElementById('spec-modal').style.display = 'flex';
            // Ensure at least one input field is present when the modal opens
            const container = document.getElementById('spec-inputs-container');
            if (container.children.length === 1 && container.children[0].style.display === 'none') {
                addSpecInput(); // Assuming a global function or moving this logic into Alpine
            }
        },
        hideSpecModal() {
            document.getElementById('spec-modal').style.display = 'none';
        },
        // ... Quick-Add AJAX functions (quickCreateItem, quickCreateSpec, addSpecInput, removeSpecInput) 
        // will remain largely the same, but should be adapted to use this.itemSpecsMap 
        // and trigger this.updateItemState(item) on relevant rows after success.
        // For brevity, I've left the original AJAX functions in the Blade file below.
        
        // --- INPUT FORMATTING HANDLERS (for the revenue input) ---
        formatTotalPayment(event) {
            const rawValue = this.parseForCalculation(event.target.value);
            event.target.value = this.formatForInput(rawValue, false);
            this.totalPayment = rawValue; // Update Alpine state
        },
    });

    // Helper functions for quick-add modals (copied from your original JS)
    // NOTE: In a production app, these should also be moved inside the Alpine component 
    // or properly structured outside the Blade file.
    let isSubtotalOverridden = false; // Kept for the static quick-add functions below

    function toggleSubtotalOverride(isChecked) {
        // This function is now superseded by the Alpine one, but kept if you didn't extract it.
        // It's cleaner to handle this entirely within Alpine (as shown in the Alpine component above).
        isSubtotalOverridden = isChecked;
        // The DOM manipulation part is now handled by Alpine's :readonly and :style bindings.
    }
    
    // ... [quickCreateItem, quickCreateSpec, addSpecInput, removeSpecInput] ... (Your original functions)
    // I will include the existing functions inside the Blade file's <script> block for completeness.
    // ...
    // START OF ORIGINAL AJAX FUNCTIONS (Minimal changes)
    // ...
    const itemSpecsMap = @json($items); // Re-introduce global map for original AJAX functions
    
    // NOTE: Since the new Alpine model manages the display, these quick-add functions 
    // must be modified to update the Alpine state (`window.orderForm`) for new items/specs
    // to appear in the dynamic table immediately.

    function quickCreateItem(event) {
        event.preventDefault();
        const form = document.getElementById('quick-item-form');
        const formData = new FormData(form);

        fetch('{{ route('items.quickStore') }}', { 
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update Alpine state: Add the new item to the map
                    window.orderForm.itemSpecsMap.push(data.item);

                    // Update all existing item selects (for the whole page)
                    const newOption = new Option(data.item.item_name, data.item.id);
                    document.querySelectorAll('.item-select').forEach(select => {
                        select.add(newOption.cloneNode(true));
                    });

                    // Update spec modal select
                    const specModalSelect = document.getElementById('spec_for_item_id');
                    if (specModalSelect) specModalSelect.add(newOption.cloneNode(true));

                    console.log('New Item "' + data.item.item_name + '" created successfully!');
                    hideItemModal();
                    form.reset();
                } else {
                    console.error('Error saving item: ' + data.message);
                }
            })
            .catch(error => {
                console.error('AJAX Error:', error);
            });
    }
    
    function addSpecInput() {
        const template = document.getElementById('spec-input-template');
        const container = document.getElementById('spec-inputs-container');
        const newSpecInput = template.cloneNode(true);
        newSpecInput.removeAttribute('id');
        newSpecInput.style.display = 'flex';
        newSpecInput.querySelector('input').value = '';
        container.appendChild(newSpecInput);
    }

    function removeSpecInput(button) {
        const container = document.getElementById('spec-inputs-container');
        const inputDiv = button.closest('.flex');
        if (container.querySelectorAll('.flex:not([style*="none"])').length > 1) {
            inputDiv.remove();
        } else {
            console.warn("You must have at least one specification detail field.");
        }
    }

    function quickCreateSpec(event) {
        event.preventDefault();
        const form = document.getElementById('quick-spec-form');
        const formData = new FormData();
        const itemId = document.getElementById('spec_for_item_id').value;

        if (!itemId) { console.error("Please select an item to associate the specification with."); return; }
        formData.append('item_id', itemId);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        const descriptionInputs = form.querySelectorAll('input[name="descriptions[]"]');
        let validSpecCount = 0;
        descriptionInputs.forEach(input => {
            const value = input.value.trim();
            if (value !== '') {
                formData.append('descriptions[]', value);
                validSpecCount++;
            }
        });

        if (validSpecCount === 0) { console.error("Please enter at least one specification detail."); return; }

        fetch('{{ route('item-specs.quickStore') }}', { 
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) return response.json().then(errorData => { throw errorData; });
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const itemToUpdate = itemSpecsMap.find(item => item.id == itemId);
                    const newSpecs = Array.isArray(data.specs) ? data.specs : [];

                    if (itemToUpdate && newSpecs.length > 0) {
                        if (!itemToUpdate.item_specs) itemToUpdate.item_specs = [];
                        newSpecs.forEach(spec => itemToUpdate.item_specs.push(spec));

                        // Force Alpine to re-render specs for matching items in the table
                        // NOTE: This assumes `window.orderForm` is accessible and initialized
                        const itemToUpdateAlpine = window.orderForm.orderItems.find(item => item.item_id == itemId);
                        if(itemToUpdateAlpine) {
                            // By updating the Alpine state, the component logic will handle the spec update
                            itemToUpdateAlpine.availableSpecs = newSpecs; 
                        }
                        
                        document.querySelectorAll('.item-select').forEach(select => {
                             // Re-initializes select2 with the new options
                            if (select.value == itemId) $(select.closest('tr').querySelector('.spec-select')).select2('destroy').select2({ placeholder: "Select specifications", allowClear: true });
                        });
                        console.log(newSpecs.length + ' New Specification(s) created and linked successfully!');
                    }

                    hideSpecModal();
                    form.reset();
                    const template = document.getElementById('spec-input-template');
                    document.getElementById('spec-inputs-container').innerHTML = template.outerHTML;
                } else {
                    console.error('Error saving specification: ' + data.message);
                }
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                if (error.errors) {
                    const errorMessages = Object.values(error.errors).map(arr => arr.join('\n')).join('\n');
                    console.error('Validation Error:\n' + errorMessages);
                } else {
                    console.error('An unexpected error occurred during specification creation.');
                }
            });
    }

    // Initialize Select2 after Alpine has rendered the first items
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
            window.InitializeSelect2 = function(element) {
                $(element).select2({
                    placeholder: "Select specifications",
                    allowClear: true
                });
            };
            // Initialize modals' select
             InitializeSelect2(document.getElementById('spec_for_item_id'));
        }
    });
</script>