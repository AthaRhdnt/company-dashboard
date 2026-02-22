<x-app-layout>
    <x-invoice-mass-edit-table
        :invoices="$outgoingInvoices"
        invoice-type="outgoing"
        title="Outgoing Invoices"
        route="outgoing-invoices.mass-update"
        export-route="outgoing-invoices.export"
    />
</x-app-layout>