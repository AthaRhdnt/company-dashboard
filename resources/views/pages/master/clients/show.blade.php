<x-app-layout>
    <x-pages.show resource="clients" :item="$client" :details="[
        'Client Name' => $client->client_name,
        'Address' => $client->address ?? 'N/A',
        'Phone Number' => $client->phone_number ?? 'N/A',
        'Fax Number' => $client->fax_number ?? 'N/A',
        'Contact Person' => $client->contact_person_name ?? 'N/A',
    ]"/>
</x-app-layout>