<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Polje :attribute mora biti prihvaćeno.',
    'accepted_if' => 'Polje :attribute mora biti prihvaćeno kada je :other :value.',
    'active_url' => 'Polje :attribute mora biti ispravan URL.',
    'after' => 'Polje :attribute mora biti datum posle :date.',
    'after_or_equal' => 'Polje :attribute mora biti datum posle ili jednak :date.',
    'alpha' => 'Polje :attribute može sadržati samo slova.',
    'alpha_dash' => 'Polje :attribute može sadržati samo slova, brojeve, crtice i donje crte.',
    'alpha_num' => 'Polje :attribute može sadržati samo slova i brojeve.',
    'any_of' => 'Polje :attribute je neispravno.',
    'array' => 'Polje :attribute mora biti niz (array).',
    'ascii' => 'Polje :attribute sme sadržati samo jednobajtne alfanumerike i simbole.',
    'before' => 'Polje :attribute mora biti datum pre :date.',
    'before_or_equal' => 'Polje :attribute mora biti datum pre ili jednak :date.',
    'between' => [
        'array' => 'Polje :attribute mora imati između :min i :max stavki.',
        'file' => 'Polje :attribute mora biti između :min i :max kilobajta.',
        'numeric' => 'Polje :attribute mora biti između :min i :max.',
        'string' => 'Polje :attribute mora imati između :min i :max karaktera.',
    ],
    'boolean' => 'Polje :attribute mora biti tačno ili netačno.',
    'can' => 'Polje :attribute sadrži nedozvoljenu vrednost.',
    'confirmed' => 'Potvrda za polje :attribute se ne poklapa.',
    'contains' => 'Polju :attribute nedostaje obavezna vrednost.',
    'current_password' => 'Lozinka je netačna.',
    'date' => 'Polje :attribute mora biti ispravan datum.',
    'date_equals' => 'Polje :attribute mora biti datum jednak :date.',
    'date_format' => 'Polje :attribute mora biti u formatu :format.',
    'decimal' => 'Polje :attribute mora imati :decimal decimala.',
    'declined' => 'Polje :attribute mora biti odbijeno.',
    'declined_if' => 'Polje :attribute mora biti odbijeno kada je :other :value.',
    'different' => 'Polja :attribute i :other moraju biti različita.',
    'digits' => 'Polje :attribute mora imati :digits cifara.',
    'digits_between' => 'Polje :attribute mora imati između :min i :max cifara.',
    'dimensions' => 'Polje :attribute ima neispravne dimenzije slike.',
    'distinct' => 'Polje :attribute sadrži duplikat vrednosti.',
    'doesnt_contain' => 'Polje :attribute ne sme da sadrži nijedno od sledećeg: :values.',
    'doesnt_end_with' => 'Polje :attribute ne sme da se završava nekim od sledećeg: :values.',
    'doesnt_start_with' => 'Polje :attribute ne sme da počinje nekim od sledećeg: :values.',
    'email' => 'Polje :attribute mora biti ispravna email adresa.',
    'ends_with' => 'Polje :attribute mora da se završava jednim od sledećeg: :values.',
    'enum' => 'Izabrano polje :attribute je neispravno.',
    'exists' => 'Izabrano polje :attribute je neispravno.',
    'extensions' => 'Polje :attribute mora imati jednu od sledećih ekstenzija: :values.',
    'file' => 'Polje :attribute mora biti fajl.',
    'filled' => 'Polje :attribute mora imati vrednost.',
    'gt' => [
        'array' => 'Polje :attribute mora imati više od :value stavki.',
        'file' => 'Polje :attribute mora biti veće od :value kilobajta.',
        'numeric' => 'Polje :attribute mora biti veće od :value.',
        'string' => 'Polje :attribute mora imati više od :value karaktera.',
    ],
    'gte' => [
        'array' => 'Polje :attribute mora imati :value ili više stavki.',
        'file' => 'Polje :attribute mora biti veće ili jednako :value kilobajta.',
        'numeric' => 'Polje :attribute mora biti veće ili jednako :value.',
        'string' => 'Polje :attribute mora imati najmanje :value karaktera.',
    ],
    'hex_color' => 'Polje :attribute mora biti ispravna heksadecimalna boja.',
    'image' => 'Polje :attribute mora biti slika.',
    'in' => 'Izabrano polje :attribute je neispravno.',
    'in_array' => 'Polje :attribute mora postojati u :other.',
    'in_array_keys' => 'Polje :attribute mora da sadrži bar jedan od sledećih ključeva: :values.',
    'integer' => 'Polje :attribute mora biti ceo broj.',
    'ip' => 'Polje :attribute mora biti ispravna IP adresa.',
    'ipv4' => 'Polje :attribute mora biti ispravna IPv4 adresa.',
    'ipv6' => 'Polje :attribute mora biti ispravna IPv6 adresa.',
    'json' => 'Polje :attribute mora biti ispravan JSON niz.',
    'list' => 'Polje :attribute mora biti lista.',
    'lowercase' => 'Polje :attribute mora biti malim slovima.',
    'lt' => [
        'array' => 'Polje :attribute mora imati manje od :value stavki.',
        'file' => 'Polje :attribute mora biti manje od :value kilobajta.',
        'numeric' => 'Polje :attribute mora biti manje od :value.',
        'string' => 'Polje :attribute mora imati manje od :value karaktera.',
    ],
    'lte' => [
        'array' => 'Polje :attribute ne sme imati više od :value stavki.',
        'file' => 'Polje :attribute mora biti manje ili jednako :value kilobajta.',
        'numeric' => 'Polje :attribute mora biti manje ili jednako :value.',
        'string' => 'Polje :attribute mora imati najviše :value karaktera.',
    ],
    'mac_address' => 'Polje :attribute mora biti ispravna MAC adresa.',
    'max' => [
        'array' => 'Polje :attribute ne sme imati više od :max stavki.',
        'file' => 'Polje :attribute ne sme biti veće od :max kilobajta.',
        'numeric' => 'Polje :attribute ne sme biti veće od :max.',
        'string' => 'Polje :attribute ne sme imati više od :max karaktera.',
    ],
    'max_digits' => 'Polje :attribute ne sme imati više od :max cifara.',
    'mimes' => 'Polje :attribute mora biti fajl tipa: :values.',
    'mimetypes' => 'Polje :attribute mora biti fajl tipa: :values.',
    'min' => [
        'array' => 'Polje :attribute mora imati najmanje :min stavki.',
        'file' => 'Polje :attribute mora imati najmanje :min kilobajta.',
        'numeric' => 'Polje :attribute mora biti najmanje :min.',
        'string' => 'Polje :attribute mora imati najmanje :min karaktera.',
    ],
    'min_digits' => 'Polje :attribute mora imati najmanje :min cifara.',
    'missing' => 'Polje :attribute mora biti izostavljeno.',
    'missing_if' => 'Polje :attribute mora biti izostavljeno kada je :other :value.',
    'missing_unless' => 'Polje :attribute mora biti izostavljeno osim ako :other nije :value.',
    'missing_with' => 'Polje :attribute mora biti izostavljeno kada je prisutno: :values.',
    'missing_with_all' => 'Polje :attribute mora biti izostavljeno kada su prisutni: :values.',
    'multiple_of' => 'Polje :attribute mora biti višekratnik broja :value.',
    'not_in' => 'Izabrano polje :attribute je neispravno.',
    'not_regex' => 'Format polja :attribute je neispravan.',
    'numeric' => 'Polje :attribute mora biti broj.',
    'password' => [
        'letters' => 'Polje :attribute mora sadržati bar jedno slovo.',
        'mixed' => 'Polje :attribute mora sadržati bar jedno veliko i jedno malo slovo.',
        'numbers' => 'Polje :attribute mora sadržati bar jednu cifru.',
        'symbols' => 'Polje :attribute mora sadržati bar jedan simbol.',
        'uncompromised' => 'Data :attribute se pojavila u curenju podataka. Molimo izaberite drugu :attribute.',
    ],
    'present' => 'Polje :attribute mora biti prisutno.',
    'present_if' => 'Polje :attribute mora biti prisutno kada je :other :value.',
    'present_unless' => 'Polje :attribute mora biti prisutno osim ako :other nije :value.',
    'present_with' => 'Polje :attribute mora biti prisutno kada je prisutno: :values.',
    'present_with_all' => 'Polje :attribute mora biti prisutno kada su prisutni: :values.',
    'prohibited' => 'Polje :attribute je zabranjeno.',
    'prohibited_if' => 'Polje :attribute je zabranjeno kada je :other :value.',
    'prohibited_if_accepted' => 'Polje :attribute je zabranjeno kada je :other prihvaćeno.',
    'prohibited_if_declined' => 'Polje :attribute je zabranjeno kada je :other odbijeno.',
    'prohibited_unless' => 'Polje :attribute je zabranjeno osim ako :other nije u: :values.',
    'prohibits' => 'Polje :attribute zabranjuje prisustvo polja :other.',
    'regex' => 'Format polja :attribute je neispravan.',
    'required' => 'Polje :attribute je obavezno.',
    'required_array_keys' => 'Polje :attribute mora sadržati unose za: :values.',
    'required_if' => 'Polje :attribute je obavezno kada je :other :value.',
    'required_if_accepted' => 'Polje :attribute je obavezno kada je :other prihvaćeno.',
    'required_if_declined' => 'Polje :attribute je obavezno kada je :other odbijeno.',
    'required_unless' => 'Polje :attribute je obavezno osim ako :other nije u: :values.',
    'required_with' => 'Polje :attribute je obavezno kada je prisutno: :values.',
    'required_with_all' => 'Polje :attribute je obavezno kada su prisutni: :values.',
    'required_without' => 'Polje :attribute je obavezno kada :values nije prisutno.',
    'required_without_all' => 'Polje :attribute je obavezno kada nijedno od :values nije prisutno.',
    'same' => 'Polje :attribute mora da se poklapa sa :other.',
    'size' => [
        'array' => 'Polje :attribute mora da sadrži :size stavki.',
        'file' => 'Polje :attribute mora biti :size kilobajta.',
        'numeric' => 'Polje :attribute mora biti :size.',
        'string' => 'Polje :attribute mora imati :size karaktera.',
    ],
    'starts_with' => 'Polje :attribute mora počinjati jednim od sledećeg: :values.',
    'string' => 'Polje :attribute mora biti tekstualni niz.',
    'timezone' => 'Polje :attribute mora biti ispravna vremenska zona.',
    'unique' => ':attribute je već zauzeto.',
    'uploaded' => 'Otpremanje za :attribute nije uspelo.',
    'uppercase' => 'Polje :attribute mora biti velikim slovima.',
    'url' => 'Polje :attribute mora biti ispravan URL.',
    'ulid' => 'Polje :attribute mora biti ispravan ULID.',
    'uuid' => 'Polje :attribute mora biti ispravan UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'prilagođena-poruka',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        // Incomes
        'user_id' => 'Korisnik',
        'name' => 'Naziv',
        'occurred_on' => 'Datum',
        'income_type_id' => 'Tip prihoda',
        'amount_minor' => 'Iznos',
        'currency_code' => 'Valuta',
        'tags' => 'Oznake',
        'tags.*' => 'Oznaka',
        'description' => 'Opis',

        // Auth
        'email' => 'Email adresa',
        'password' => 'Lozinka',
        'password_confirmation' => 'Potvrda lozinke',
        'current_password' => 'Trenutna lozinka',
        'token' => 'Token',
        'code' => 'Kod',
        'recovery_code' => 'Kod za oporavak',

        // Settings
        'locale' => 'Jezik',
        'password_confirm_minutes' => 'Vremensko ograničenje potvrde lozinke',
    ],

];
