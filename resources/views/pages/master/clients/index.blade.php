<x-app-layout>
    <x-pages.index
        resource="clients"
        :items="$clients"
        :headers="[
            'client_name' => 'Client Name',
            'contact_person_name' => 'Contact Person',
            'phone_number' => 'Phone Number'
        ]"
    />
</x-app-layout>