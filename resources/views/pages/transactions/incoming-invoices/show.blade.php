<x-app-layout>
    <x-invoice-detail-view
        :invoice="$invoice"
        title="Incoming Invoice Detail (Vendor Cost)"
        theme-color="red"
        subject-header="Vendor Name"
        subject-property="vendor.vendor_name"
        date1-header="Invoice Received Date"
        date1-property="inv_received_date"
        date2-header="FP Date"
        date2-property="fp_date"
        edit-route-name="incoming-invoices.edit"
    />
</x-app-layout>