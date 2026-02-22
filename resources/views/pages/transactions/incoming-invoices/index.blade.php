<x-app-layout>
    <x-invoice-mass-edit-table
        :invoices="$incomingInvoices"
        invoice-type="incoming"
        title="Incoming Invoices"
        route="incoming-invoices.mass-update"
        export-route="incoming-invoices.export"
    />
</x-app-layout>