<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

$settings = [
    'version' => 1,
    'options_page' => [
        'page' => [
            'title' => __('Legal', 'rrze-legal'),
        ],
        'menu' => [
            'title' => __('Legal', 'rrze-legal'),
            'capability' => 'manage_network_options',
            'slug' => 'legal',
        ],
    ],
    'settings' => [
        'title' => __('Legal Settings', 'rrze-legal'),
        'sections' => [
            [
                'id' => 'default_values',
                'title' => __('Default Values', 'rrze-legal'),
                'description' => '',
                'fields' => [
                    [
                        'name' => 'fau_domains',
                        'label' => __('FAU domains', 'rrze-legal'),
                        'description' => __('Websites whose URLs contain default domains will get the default values.', 'rrze-legal'),
                        'type' => 'textarea',
                        'default' => settings()->getDefaultDomainsToString(),
                        'sanitize_callback' => [settings(), 'sanitizeTextareaList'],
                    ],
                ],
            ],
        ],
    ],
];
