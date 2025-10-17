<x-app-layout>
    <x-invoice-mass-edit-table
        :invoices="$outgoingInvoices"
        title="Outgoing Invoices"
        route="outgoing-invoices.mass-update"
        theme-color="indigo"
        header-bg-class="bg-indigo-50"
        subject-header="CHARGED TO"
        subject-property="client.client_name"
        date1-header="INV DATE"
        date1-property="inv_date"
        date2-header="DUE DATE"
        date2-property="due_date"
        save-button-color="green"
        export-route="outgoing-invoices.export"
    />
</x-app-layout>