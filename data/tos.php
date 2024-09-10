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
                'phone'             => '+49 9131 85 0',
                'fax'               => '',
                'postal_street'     => 'Schloßplatz 4',
                'postal_code'       => '91054',
                'postal_city'       => 'Erlangen',
                'person_name'       => 'Präsident Prof. Dr. Joachim Hornegger',
            ],
            'supervisory_authority' => [
                '_readonly'        => true,
                'name'              => 'Bayerisches Staatsministerium für Wissenschaft und Kunst',
                'email'             => '',
                'postal_street'     => 'Salvatorstraße 2',
                'postal_code'       => '80327',
                'postal_city'      => 'München',
                'url'               => 'https://www.stmwk.bayern.de/',
            ],
            'id_numbers'    => [
                '_readonly'        => true,
                'ustg'              => 'DE 132507686',
                'tax'               => '216/114/20045 (Finanzamt Erlangen)',
                'duns'              => '327958716',
                'eori'              => 'DE4204891',               
            ],
            'it_security' => [
                '_readonly'         => true,
                'name'              => '',
                'email'             => 'abuse@fau.de',
                'postal_street'     => '',
                'postal_code'       => '',
                'postal_city'       => '',
                'url'               => 'https://www.rrze.fau.de/abuse/',
            ],
            'whistleblower_system' => [
                '_readonly'        => true,
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
            ]
            
            
  
            
        ],
        'utn'   => [
            'name' => __('University of Technology Nuremberg ', 'rrze-legal'),
             'imprint_representation'    => [
                '_readonly'         => true,
                'email'             => 'president@utn.de',
                'name'              => 'Technische Universität Nürnberg',
                'phone'             => '',
                'fax'               => '',
                'postal_street'     => 'Ulmenstraße 52i',
                'postal_code'       => '90443',
                'postal_city'       => 'Nürnberg ',
                'person_name'       => 'Präsident Prof. Dr. Alexander Martin',
            ],
            'supervisory_authority' => [
                '_readonly'        => true,
                'name'              => 'Bayerisches Staatsministerium für Wissenschaft und Kunst',
                'email'             => '',
                'postal_street'     => 'Salvatorstraße 2',
                'postal_code'       => '80327',
                'postal_city'      => 'München',
                'url'               => 'https://www.stmwk.bayern.de/',
            ],
             'privacy_dpo' =>  [
                '_readonly'        => true,
                'email'             => 'dataprotection@utn.de',
                'name'              => 'insidas GmbH & Co. KG vertreten durch Kilian Bauer',
                'fax'               => '',
                'phone'             => '+49 871 20 54 94 0',
                'postal_co'         => '',
                'postal_street'     => 'Wallerstraße 2',
                'postal_code'       => '84032 ',
                'postal_city'       => 'Altdorf ',
                'person_name'       => '',
            ]
        ],
        'uk'    => [
            'name'  => __('Universitätsklinikum Erlangen', 'rrze-legal'),
        ],
        'stw'   => [
            'name' => __('Studierendenwerk Erlangen-Nürnberg', 'rrze-legal'),
        ],
        'cooperation'   => [
            'name' => __('Cooperation between different institutions', 'rrze-legal'),
        ],
        'external'  => [
            'name' => __('External institution', 'rrze-legal'),
        ]
        
       
    ],
];
