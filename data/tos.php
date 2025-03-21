<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

$data = [
    'version' => 1,
    'items' => [
        'fau'   => [
            'name' => 'Friedrich-Alexander-Universität Erlangen-Nürnberg',
        
            'imprint_representation'    => [
                '_readonly'         => true,
                'email'             => 'poststelle@fau.de',
                'name'              => 'Friedrich-Alexander-Universität Erlangen-Nürnberg',
                'phone'             => '',
                'fax'               => '',
                'postal_street'     => 'Freyeslebenstraße 1',
                'postal_code'       => '91058',
                'postal_city'       => 'Erlangen',
                'person_name'       => 'Präsident Prof. Dr. Joachim Hornegger',
                'legal-label'       => 'Körperschaft des Öffentlichen Rechts',
            ],
            'imprint_supervisory_authority' => [
                '_readonly'        => true,
                'name'              => 'Bayerisches Staatsministerium für Wissenschaft und Kunst',
                'email'             => '',
                'postal_street'     => 'Salvatorstraße 2',
                'postal_code'       => '80327',
                'postal_city'      => 'München',
                'url'               => 'https://www.stmwk.bayern.de/',
                'phone'             => '',
                'fax'               => '',
            ],
            'imprint_id_numbers'    => [
                '_readonly'        => true,
                'ustg'              => 'DE 132507686',
                'tax'               => '216/114/20045 (Finanzamt Erlangen)',
                'duns'              => '327958716',
                'eori'              => 'DE4204891',              
                'bankname'          => 'Bayerische Landesbank München',             
                'iban'              => 'DE66700500000301279280',
                'bic'               => 'BYLADEMMXXX'
            ],
            'imprint_it_security' => [
                '_readonly'         => true,
                'name'              => 'IT-Sicherheit',
                'email'             => '',
                'phone'             => '',
                'url'               => 'https://www.rrze.fau.de/abuse/',
                'postal_co'         => '',
                'postal_street'     => 'Martensstraße 1',
                'postal_code'       => '91058',
                'postal_city'      => 'Erlangen',
            ],
            'imprint_whistleblower_system' => [
                '_readonly'        => true,
                'linktitle'         => 'Hinweisgebersystem der FAU',
                'url'               => 'https://fau.whistletrust.eu/startWhistleProcess.php'
            ],
            'privacy_dpo' =>  [
                '_readonly'        => true,
                'email'             => 'datenschutzbeauftragter@fau.de',
                'name'              => 'Datenschutzbeauftragter FAU',
                'fax'               => '',
                'phone'             => '+49 9131 85-25860',
                'postal_co'         => 'c/o ITM Gesellschaft für IT-Management mbH',
                'postal_street'     => 'Bürgerstraße 81',
                'postal_code'       => '01127',
                'postal_city'      => 'Dresden',
                'person_name'       => 'Klaus Hoogestraat',
            ],
            'accessibility_supervisory_authority' => [
                '_readonly'        => true,
                'name'              => 'Landesamt für Digitalisierung, Breitband und Vermessung',
                'email'             => 'bitv@bayern.de',
                'phone'             => '',
                'fax'               => '',
                'postal_co'         => 'IT-Dienstleistungszentrum des Freistaats Bayern - Durchsetzungs- und Überwachungsstelle für barrierefreie Informationstechnik',
                'postal_street'     => 'St.-Martin-Straße 47',
                'postal_code'       => '81541',
                'postal_city'       => 'München',
                'url'               => 'https://www.ldbv.bayern.de/digitalisierung/bitv/',
                'url_law'               => 'https://www.gesetze-bayern.de/Content/Document/BayDiG',
                'url_vo'               => 'https://www.gesetze-bayern.de/Content/Document/BayDiV',
            ],
   
            
        ],
        'utn'   => [
            'name' => __('University of Technology Nuremberg ', 'rrze-legal'),
             'imprint_representation'    => [
                '_readonly'         => true,
                'email'             => 'president@utn.de',
                'name'              => 'Technische Universität Nürnberg',
                'phone'             => '',
                'fax'               => '',
                'postal_street'     => 'Dr.-Luise-Herzberg-Straße 4',
                'postal_code'       => '90461',
                'postal_city'       => 'Nürnberg ',
                'person_name'       => 'Präsident Prof. Dr. Michael Huth',
                'legal-label'       => 'Körperschaft des Öffentlichen Rechts'
            ],
            'imprint_supervisory_authority' => [
                '_readonly'        => true,
                'name'              => 'Bayerisches Staatsministerium für Wissenschaft und Kunst',
                'email'             => '',
                'postal_street'     => 'Salvatorstraße 2',
                'postal_code'       => '80327',
                'postal_city'      => 'München',
                'url'               => 'https://www.stmwk.bayern.de/',
            ],
            'imprint_whistleblower_system' => [
                '_readonly'        => false,
                'linktitle'         => '',
                'url'               => ''
            ],
             'imprint_it_security' => [
                '_readonly'         => false,
                'name'              => '',
                'email'             => '',
                'phone'             => '',
                'url'               => '',
                'postal_co'         => '',
                'postal_street'     => '',
                'postal_code'       => '',
                'postal_city'      => '',
            ],
             'privacy_dpo' =>  [
                '_readonly'        => true,
                'email'             => 'dataprotection@utn.de',
                'name'              => 'insidas GmbH & Co. KG vertreten durch Kilian Bauer',
                'fax'               => '',
                'phone'             => '+49 871 20 54 94 0',
                'postal_co'         => '',
                'postal_street'     => 'Wallerstraße 2',
                'postal_code'       => '84032',
                'postal_city'       => 'Altdorf ',
                'person_name'       => '',
            ],
            'accessibility_supervisory_authority' => [
                '_readonly'        => true,
                'name'              => 'Landesamt für Digitalisierung, Breitband und Vermessung',
                'email'             => 'bitv@bayern.de',
                'phone'             => '',
                'fax'               => '',
                'postal_co'         => 'IT-Dienstleistungszentrum des Freistaats Bayern - Durchsetzungs- und Überwachungsstelle für barrierefreie Informationstechnik',
                'postal_street'     => 'St.-Martin-Straße 47',
                'postal_code'       => '81541',
                'postal_city'      => 'München',
                'url'               => 'https://www.ldbv.bayern.de/digitalisierung/bitv/',
                'url_law'               => 'https://www.gesetze-bayern.de/Content/Document/BayDiG',
                'url_vo'               => 'https://www.gesetze-bayern.de/Content/Document/BayDiV',
            ],
        ],
        'uk'    => [
            'name'  => __('Universitätsklinikum Erlangen', 'rrze-legal'),
             'imprint_representation'    => [
                '_readonly'         => true,
                'email'             => '',
                'name'              => 'Universitätsklinikum Erlangen',
                'phone'             => '+49 9131 85 0',
                'fax'               => '',
                'postal_street'     => 'Maximiliansplatz 2',
                'postal_code'       => '91054',
                'postal_city'       => 'Erlangen',
                'person_name'       => 'Ärztlicher Direktor Prof. Dr. med. Dr. h. c. Heinrich Iro',
                'legal-label'       => 'Körperschaft des Öffentlichen Rechts'
            ],
            'imprint_supervisory_authority' => [
                '_readonly'        => true,
                'name'              => 'Bayerisches Staatsministerium für Wissenschaft und Kunst',
                'email'             => '',
                'postal_street'     => 'Salvatorstraße 2',
                'postal_code'       => '80327',
                'postal_city'      => 'München',
                'url'               => 'https://www.stmwk.bayern.de/',
            ],
            'imprint_id_numbers'    => [
                '_readonly'        => true,
                'ustg'              => 'DE 248558812',
                'tax'               => '',
                'duns'              => '',
                'eori'              => '',
                'iban'              => '',
                'bic'               => ''
            ],
            'imprint_it_security' => [
                '_readonly'         => false,
                'name'              => '',
                'email'             => '',
                'phone'             => '',
                'url'               => '',
                'postal_co'         => '',
                'postal_street'     => '',
                'postal_code'       => '',
                'postal_city'      => '',
            ],
            'imprint_whistleblower_system' => [
                '_readonly'        => false,
                'linktitle'         => '',
                'url'               => ''
            ],
              'privacy_dpo' =>  [
                '_readonly'        => true,
                'email'             => 'datenschutz@uk-erlangen.de',
                'name'              => 'Datenschutzbeauftragter',
                'fax'               => '',
                'phone'             => '',
                'postal_co'         => '',
                'postal_street'     => 'Krankenhausstr. 12',
                'postal_code'       => '91054',
                'postal_city'       => 'Erlangen',
                'person_name'       => '',
            ],
            'accessibility_supervisory_authority' => [
                '_readonly'        => true,
                'name'              => 'Landesamt für Digitalisierung, Breitband und Vermessung',
                'email'             => 'bitv@bayern.de',
                'phone'             => '',
                'fax'               => '',
                'postal_co'         => 'IT-Dienstleistungszentrum des Freistaats Bayern - Durchsetzungs- und Überwachungsstelle für barrierefreie Informationstechnik',
                'postal_street'     => 'St.-Martin-Straße 47',
                'postal_code'       => '81541',
                'postal_city'      => 'München',
                'url'               => 'https://www.ldbv.bayern.de/digitalisierung/bitv/',
                'url_law'               => 'https://www.gesetze-bayern.de/Content/Document/BayDiG',
                'url_vo'               => 'https://www.gesetze-bayern.de/Content/Document/BayDiV',
            ],
        ],

        'cooperation'   => [
            'name' => __('Cooperation between different institutions', 'rrze-legal'),
        ],
        'external'  => [
            'name' => __('External institution', 'rrze-legal'),
        ]
        
       
    ],
];
