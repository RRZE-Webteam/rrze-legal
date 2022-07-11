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
            'title' => __('Consent Cookies', 'rrze-legal'),
        ],
        'menu' => [
            'title' => __('Consent Cookies', 'rrze-legal'),
            'capability' => apply_filters('rrze_legal_consent_capability', 'manage_options'),
            'slug' => 'consent-cookies',
            'position' => 20,
        ],
    ],
    'settings' => [
        'title' => __('Consent Cookies', 'rrze-legal'),
        'sections' => [
            [
                'id' => 'consent_cookies',
                'title' => apply_filters('rrze_legal_section_consent_edit_new_title', __('Add New Cookie', 'rrze-legal')),
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
                            return consentCookies()->validateId($input);
                        },
                        'required' => true,
                    ],
                    [
                        'name' => 'category',
                        'label' => __('Category', 'rrze-legal'),
                        'description' => __('The <strong>Consent Category</strong> the <strong>Cookie</strong> is part of.', 'rrze-legal'),
                        'type' => 'select',
                        'options' => consentCategories()->getItemsOptions(),
                        'default' => 'external_media',
                    ],
                    [
                        'name' => 'status',
                        'label' => __('Status', 'rrze-legal'),
                        'description' => __('The status of this <strong>Cookie</strong>. If enabled (Status: Enabled) it is displayed to the visitor in the <strong>Consent Banner</strong>', 'rrze-legal'),
                        'type' => 'checkbox',
                        'default' => '0',
                    ],
                    [
                        'name'              => 'position',
                        'label'             => __('Position', 'rrze-legal'),
                        'desc'              => __('Determine the position where this <strong>Cookie</strong> is displayed in the <strong>Consent Category</strong>. Order follows natural numbers.', 'rrze-legal'),
                        'placeholder'       => '1',
                        'min'               => '1',
                        'max'               => '99',
                        'step'              => '1',
                        'type'              => 'number',
                        'default'           => '1',
                        'sanitize_callback' => function ($input) {
                            return consentCookies()->validateIntRange($input, 1, 99);
                        },
                    ],
                    [
                        'name' => 'name',
                        'label' => __('Name', 'rrze-legal'),
                        'description' => __('Insert a name for this <strong>Cookie</strong>.', 'rrze-legal'),
                        'type' => 'text',
                        'default' => '',
                        'sanitize_callback' => 'sanitize_text_field',
                        'required' => true,
                    ],
                    [
                        'name' => 'provider',
                        'label' => __('Provider', 'rrze-legal'),
                        'description' => __('Insert the provider of this <strong>Cookie</strong>.', 'rrze-legal'),
                        'type' => 'text',
                        'default' => '',
                        'sanitize_callback' => 'sanitize_text_field',
                        'required' => true,
                    ],
                    [
                        'name' => 'purpose',
                        'label' => __('Purpose', 'rrze-legal'),
                        'description' => __('Explain the purpose of this <strong>Cookie</strong> to the visitors.', 'rrze-legal'),
                        'type' => 'textarea',
                        'default' => '',
                        'sanitize_callback' => 'sanitize_textarea_field',
                        'required' => true,
                    ],
                    [
                        'name' => 'privacy_policy_url',
                        'label' => __('Privacy Policy URL', 'rrze-legal'),
                        'description' => __('Provide a URL to the privacy policy of the provider of the <strong>Cookie</strong>.', 'rrze-legal'),
                        'type' => 'text',
                        'placeholder' => 'https://',
                        'sanitize_callback' => 'sanitize_url',
                        'required' => true,
                    ],
                    [
                        'name' => 'hosts',
                        'label' => __('Host(s)', 'rrze-legal'),
                        'description' => __('Insert the host(s) of this <strong>Cookie</strong>.', 'rrze-legal'),
                        'type' => 'textarea',
                        'default' => '',
                        'sanitize_callback' => [consentCookies(), 'sanitizeTextareaList'],
                    ],
                    [
                        'name' => 'cookie_name',
                        'label' => __('Cookie Name', 'rrze-legal'),
                        'description' => __('Provide the technical name of the <strong>Cookie</strong>. Separate multiple cookie names with a comma.', 'rrze-legal'),
                        'type' => 'text',
                        'default' => '',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    [
                        'name' => 'cookie_expiry',
                        'label' => __('Cookie Expiry', 'rrze-legal'),
                        'description' => __('Provide the expiry date of the <strong>Cookie</strong>.', 'rrze-legal'),
                        'type' => 'text',
                        'default' => '',
                        'sanitize_callback' => 'sanitize_text_field',
                        'required' => true,
                    ],
                    [
                        'name' => 'enqueued_script_handles',
                        'label' => __('Enqueued Script Name', 'rrze-legal'),
                        'description' => __('Name of the enqueued script. It is required if you want to block the script. Separate multiple names with a comma.', 'rrze-legal'),
                        'type' => 'text',
                        'default' => '',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    [
                        'name' => 'block_enqueued_script',
                        'label' => __('Block Enqueued Script', 'rrze-legal'),
                        'description' => __('The enqueued script will be blocked', 'rrze-legal'),
                        'type' => 'checkbox',
                        'default' => '0',
                    ],
                    [
                        'name' => 'prioritize',
                        'label' => __('Prioritize Opt-in Code', 'rrze-legal'),
                        'description' => __('The <strong>Opt-in Code</strong> is loaded in <head> and is executed before the page is fully loaded', 'rrze-legal'),
                        'type' => 'checkbox',
                        'default' => '0',
                    ],
                    [
                        'name' => 'async_opt_out_code',
                        'label' => __('Asynchronous Opt-Out Code', 'rrze-legal'),
                        'description' => __('The <strong>Opt-Out Code</strong> contains asynchronous JavaScript code that needs to executed to finish the Opt-Out', 'rrze-legal'),
                        'type' => 'checkbox',
                        'default' => '0',
                    ],
                    [
                        'name' => 'opt_in_js',
                        'label' => __('Opt-in Code', 'rrze-legal'),
                        'description' => __('This code will be executed after the visitor gives their consent.', 'rrze-legal'),
                        'type' => 'htmleditor',
                        'default' => '',
                        'sanitize_callback' => 'stripslashes',
                    ],
                    [
                        'name' => 'opt_out_js',
                        'label' => __('Opt-out Code', 'rrze-legal'),
                        'description' => __('This code will be executed only if the visitor did opt-in previously and chooses to opt-out. It is executed once.', 'rrze-legal'),
                        'type' => 'htmleditor',
                        'default' => '',
                        'sanitize_callback' => 'stripslashes',
                    ],
                    [
                        'name' => 'opt_in_js',
                        'label' => __('Opt-in Code', 'rrze-legal'),
                        'description' => __('This code will be executed after the visitor gives their consent.', 'rrze-legal'),
                        'type' => 'htmleditor',
                        'default' => '',
                        'sanitize_callback' => 'stripslashes',
                    ],
                    [
                        'name' => 'fallback_js',
                        'label' => __('Fallback Code', 'rrze-legal'),
                        'description' => __('This code will always be executed.', 'rrze-legal'),
                        'type' => 'htmleditor',
                        'default' => '',
                        'sanitize_callback' => 'stripslashes',
                    ],
                ],
            ],
        ]
    ],
];
