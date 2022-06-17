<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

$settings = [
    'version' => 1,
    'options_page' => [
        'parent' => [
            'slug' => 'legal',
        ],
        'page' => [
            'title' => __('Consent Categories', 'rrze-legal'),
        ],
        'menu' => [
            'title' => __('Consent Categories', 'rrze-legal'),
            'capability' => apply_filters('rrze_legal_consent_capability', 'manage_options'),
            'slug' => 'consent-categories',
            'position' => 20,
        ],
    ],
    'settings' => [
        'title' => __('Consent Categories', 'rrze-legal'),
        'sections' => [
            [
                'id' => 'consent_categories',
                'title' => apply_filters('rrze_legal_section_consent_edit_new_title', __('Add New Category', 'rrze-legal')),
                'description' => '',
                'fields' => [
                    [
                        'name' => 'id',
                        'label' => __('ID', 'rrze-legal'),
                        'description' => __('The <strong>ID</strong> must be at least 3 characters long and may only contain <code>a-z_-</code> characters.', 'rrze-legal'),
                        'type' => 'text',
                        'default' => '',
                        'size' => 'normal',
                        'disabled' => false,
                        'sanitize_callback' => function ($input) {
                            return consentCategories()->validateId($input);
                        },
                        'required' => true,
                    ],
                    [
                        'name' => 'status',
                        'label' => __('Status', 'rrze-legal'),
                        'description' => __('The status of this <strong>Consent Category</strong>. If enabled (Status: Enabled) it is displayed to the visitor in the <strong>Consent Banner</strong>', 'rrze-legal'),
                        'type' => 'checkbox',
                        'default' => '0',
                    ],
                    [
                        'name' => 'name',
                        'label' => __('Name', 'rrze-legal'),
                        'description' => __('Enter a name for this <strong>Consent Category</strong>. It is displayed to the visitor in the <strong>Consent Banner</strong>.', 'rrze-legal'),
                        'type' => 'text',
                        'default' => '',
                        'sanitize_callback' => 'sanitize_text_field',
                        'required' => true,
                    ],
                    [
                        'name' => 'description',
                        'label' => __('Description', 'rrze-legal'),
                        'description' => __('Enter a description for this <strong>Consent Category</strong>. It is displayed to the visitor in the <strong>Consent Banner</strong>.', 'rrze-legal'),
                        'type' => 'textarea',
                        'default' => '',
                        'sanitize_callback' => 'sanitize_textarea_field',
                        'required' => true,
                    ],
                    [
                        'name' => 'preselected',
                        'label' => __('Preselected', 'rrze-legal'),
                        'description' => __('If enabled (Status: Enabled) this <strong>Consent Category</strong> is preselected in the <strong>Consent Banner</strong>. The visitor can deselect it', 'rrze-legal'),
                        'type' => 'checkbox',
                        'default' => '0',
                    ],
                    [
                        'name'              => 'position',
                        'label'             => __('Position', 'rrze-legal'),
                        'desc'              => __('Determine the position where this <strong>Consent Category</strong> is displayed. Order follows natural numbers.', 'rrze-legal'),
                        'placeholder'       => '1',
                        'min'               => '1',
                        'max'               => '99',
                        'step'              => '1',
                        'type'              => 'number',
                        'default'           => '1',
                        'sanitize_callback' => function ($input) {
                            return consentCategories()->validateIntRange($input, 1, 99);
                        },
                    ],
                ],
            ],
        ]
    ],
];
