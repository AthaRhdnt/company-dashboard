<x-app-layout>
    <x-invoice-detail-view
        :invoice="$invoice"
        title="Outgoing Invoice Detail"
        theme-color="indigo"
        subject-header="Client Name"
        subject-property="client.client_name"
        date1-header="Invoice Date"
        date1-property="inv_date"
        date2-header="Due Date"
        date2-property="due_date"
        edit-route-name="outgoing-invoices.edit"
    />
</x-app-layout>