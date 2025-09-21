<?php

return [
    'title' => 'Prihodi',
    'description' => 'Upravljajte vašim prihodima. Dodajte platu, bonus i ostale prihode. Filtrirajte po periodu, tipu i valuti.',
    'details' => 'Detalji prihoda',
    'details_title' => 'Detalji prihoda',

    'actions' => [
        'add' => 'Dodaj prihod',
        'save' => 'Sačuvaj',
        'cancel' => 'Otkaži',
        'discard' => 'Odbaci izmene',
        'edit' => 'Izmeni',
        'delete' => 'Obriši',
        'view' => 'Pregled',
    ],

    'filters' => [
        'month' => 'Mesec',
        'year' => 'Godina',
        'type' => 'Tip',
        'currency' => 'Valuta',
        'all' => 'Sve',
        'toggle' => 'Filteri',
        'convert' => 'Konvertuj sve iznose',
        'display_currency' => 'Valuta prikaza',
    ],

    'types' => [
        'salary' => 'Plata',
        'bonus' => 'Bonus',
        'other' => 'Ostalo',

        // Poruke za CRUD nad tipovima prihoda
        'created' => 'Tip prihoda je kreiran.',
        'updated' => 'Tip prihoda je ažuriran.',
        'deleted' => 'Tip prihoda je obrisan.',
        'cannot_delete_linked' => 'Ovaj tip prihoda nije moguće obrisati jer se koristi na postojećim prihodima.',
        'cannot_delete_system' => 'Sistemske tipove prihoda nije moguće obrisati.',
    ],

    'table' => [
        'date' => 'Datum',
        'name' => 'Naziv',
        'description' => 'Opis',
        'type' => 'Tip',
        'amount' => 'Iznos',
        'currency' => 'Valuta',
        'actions' => 'Akcije',
        'empty' => 'Nema prihoda za izabrane filtere. Dodajte svoj prvi prihod da počnete.',
    ],

    'form' => [
        'name' => 'Naziv',
        'name_placeholder' => 'npr. Mesečna plata',
        'tags' => 'Tagovi',
        'tags_placeholder' => 'Ukucajte i pritisnite Enter da dodate tag',
        'amount' => 'Iznos',
        'description' => 'Opis (opciono)',
        'description_placeholder' => 'npr. Mesečna plata za januar 2020',
        'occurred_on' => 'Datum',
        'income_type_key' => 'Tip prihoda',
        'currency_code' => 'Valuta',
        'notes' => 'Beleške',
        'add_type' => 'Dodaj novi tip',
        'add_type_title' => 'Dodaj tip prihoda',
        'add_type_desc' => 'Kreirajte prilagođeni tip prihoda za kasniju upotrebu.',
        'new_type_label' => 'Naziv tipa',
        'new_type_placeholder' => 'npr. Honorari, Freelance, Poklon',
        'manage_types_hint' => 'Savet: Za detaljnije upravljanje tipovima, koristite stranicu ispod.',
        'manage_types_link' => 'Upravljaj tipovima prihoda',
    ],
    'confirm' => [
        'delete_title' => 'Brisanje prihoda',
        'delete_description' => 'Da li ste sigurni da želite da obrišete ovaj prihod? Ova radnja je nepovratna.',
        'discard_title' => 'Odbaciti izmene?',
        'discard_description' => 'Imate nesačuvane izmene. Da li želite da ih odbacite?',
    ],
    'original_value' => 'Originalni iznos: :value',

    // Poruke (toast)
    'toasts' => [
        'created' => 'Novi prihod je dodat.',
        'updated' => 'Prihod je ažuriran.',
        'deleted' => 'Prihod ":name" je obrisan.',
        'delete_failed' => 'Brisanje prihoda nije uspelo.',
        'delete_forbidden' => 'Nemate dozvolu da obrišete ovaj prihod.',
    ],
];
