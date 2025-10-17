<x-app-layout>
    <x-invoice-mass-edit-table
        :invoices="$incomingInvoices"
        title="Incoming Invoices (Vendor Cost)"
        route="incoming-invoices.mass-update"
        theme-color="red"
        header-bg-class="bg-red-50"
        subject-header="SUPPLIER / SUBCON"
        subject-property="vendor.vendor_name"
        date1-header="INV RCVD DATE"
        date1-property="inv_received_date"
        date2-header="INV / FP DATE"
        date2-property="fp_date"
        save-button-color="red"
        export-route="incoming-invoices.export"
    />
</x-app-layout>