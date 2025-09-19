<?php

return [
    'title' => 'Podešavanja',
    'description' => 'Upravljajte podešavanjima profila i naloga',

    'nav' => [
        'profile' => 'Profil',
        'password' => 'Lozinka',
        'appearance' => 'Podešavanja izgleda',
    ],

    'profile' => [
        'title' => 'Profil',
        'profile_information' => 'Informacije o profilu',
        'profile_information_desc' => 'Ažurirajte vaše ime i email adresu',

        'name' => 'Ime',
        'email' => 'Email adresa',
        'language' => 'Jezik',
        'english' => 'Engleski',
        'serbian' => 'Srpski',
        'default_currency' => 'Podrazumevana valuta',
        'full_name' => 'Puno ime',

        'require_password_after_inactivity' => 'Zahtevaj lozinku nakon neaktivnosti',
        'require_password_hint' => 'Od vas će biti zatraženo da potvrdite lozinku nakon neaktivnosti u izabranom periodu.',
        'inactivity_off' => 'Isključeno',
        'inactivity_30' => 'Posle 30 minuta',
        'inactivity_60' => 'Posle 1 sata',
        'inactivity_240' => 'Posle 4 sata',
        'inactivity_600' => 'Posle 10 sati',

        'email_unverified' => 'Vaša email adresa nije verifikovana.',
        'resend_verification' => 'Kliknite ovde da ponovo pošaljete verifikacioni email.',
        'verification_link_sent' => 'Novi verifikacioni link je poslat na vašu email adresu.',

        // Two-Factor section
        'two_factor' => 'Dvofaktorska autentikacija',
        'two_factor_desc' => 'Dodajte dodatni sloj bezbednosti vašem nalogu.',
        'status_enabled' => 'Omogućeno',
        'status_setting_up' => 'Podešavanje',
        'status_disabled' => 'Onemogućeno',

        // Sessions card
        'sessions_card_title' => 'Aktivne sesije',
        'sessions_card_desc' => 'Pregledajte i odjavite uređaje prijavljene na vaš nalog.',

        // Delete account
        'delete_account' => 'Obriši nalog',
        'delete_account_desc' => 'Obrišite svoj nalog i sve njegove resurse',
        'delete_warning' => 'Upozorenje',
        'delete_warning_desc' => 'Nastavite sa oprezom, ovo se ne može poništiti.',
        'delete_confirm_title' => 'Da li ste sigurni da želite da obrišete svoj nalog?',
        'delete_confirm_desc' => 'Nakon brisanja naloga, svi resursi i podaci biće trajno obrisani. Unesite lozinku da potvrdite da želite trajno da obrišete svoj nalog.',
    ],

    'appearance' => [
        'title' => 'Podešavanja izgleda',
        'description' => 'Ažurirajte podešavanja izgleda vašeg naloga',
    ],

    'password' => [
        'title' => 'Lozinka',
        'update_password' => 'Ažuriraj lozinku',
        'update_password_desc' => 'Kako biste ostali bezbedni, koristite dugu, nasumičnu lozinku',

        'current_password' => 'Trenutna lozinka',
        'new_password' => 'Nova lozinka',
        'confirm_password' => 'Potvrdi lozinku',

        'placeholder_current' => 'Trenutna lozinka',
        'placeholder_new' => 'Nova lozinka',
        'placeholder_confirm' => 'Potvrdi lozinku',

        'save_password' => 'Sačuvaj lozinku',
    ],

    'sessions' => [
        'title' => 'Aktivne sesije',
        'description' => 'Pregledajte uređaje koji su trenutno prijavljeni na vaš nalog.',
        'no_active' => 'Nema aktivnih sesija.',
        'unknown_ip' => 'Nepoznata IP adresa',
        'current' => 'Trenutna',
        'device_suffix' => ':label uređaj',
        'last_active' => 'Poslednja aktivnost: :time',
        'phone' => 'Telefon',
        'tablet' => 'Tablet',
        'computer' => 'Računar',
    ],

    'security' => [
        'title' => 'Bezbednosna podešavanja',
        'two_factor' => 'Dvofaktorska autentikacija',
        'two_factor_desc' => 'Dodajte dodatni sloj bezbednosti vašem nalogu.',

        'enabled_using' => '2FA je omogućena putem :type.',

        'email_code_title' => 'Email kod',
        'email_code_desc' => 'Prilikom prijave primićete šestocifreni kod na svoju email adresu.',

        'totp_title' => 'Aplikacija autentifikatora (TOTP)',
        'totp_desc' => 'Koristite Google Authenticator ili kompatibilnu aplikaciju za generisanje šestocifrenih kodova.',

        'recovery_codes_title' => 'Sačuvajte ove kodove za oporavak',
        'recovery_codes_desc' => 'Sačuvajte ove jednokratne kodove na bezbedno mesto. Svaki kod se može upotrebiti jednom ako izgubite pristup aplikaciji autentifikatora.',

        'tip_lost_access' => 'Savet: Ako omogućite 2FA i izgubite pristup, moraćete da kontaktirate podršku ili da koristite email 2FA da povratite pristup.',

        'email_confirm_title' => 'Potvrdite email 2FA',
        'email_confirm_desc' => 'Poslali smo šestocifreni kod na vašu email adresu. Unesite ga ispod da omogućite email dvofaktorsku autentikaciju.',
        'authentication_code' => 'Autentikacioni kod',

        'totp_setup_title' => 'Podesite aplikaciju autentifikatora',
        'totp_scan_alt' => 'Skenirajte ovaj QR u aplikaciji autentifikatora',
        'enter_code_to_confirm' => 'Unesite kod za potvrdu',

        'begin_setup_desc' => 'Kliknite na dugme ispod da započnete podešavanje i generišete svoj QR kod.',

        // Recovery codes actions
        'copy_codes' => 'Kopiraj kodove',
        'copied_recovery_codes' => 'Kodovi za oporavak su kopirani',
        'copy_failed' => 'Kopiranje nije uspelo. Pokušajte ponovo.',
    ],
];
