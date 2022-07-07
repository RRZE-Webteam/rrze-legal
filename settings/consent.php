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
            'title' => __('Consent Banner', 'rrze-legal'),
        ],
        'menu' => [
            'title' => __('Consent Banner', 'rrze-legal'),
            'capability' => apply_filters('rrze_legal_consent_capability', 'manage_options'),
            'slug' => 'consent',
            'position' => 15,
        ],
    ],
    'settings' => [
        'title' => __('Consent Settings', 'rrze-legal'),
        'sections' => [
            [
                'id' => 'banner',
                'title' => __('Banner', 'rrze-legal'),
                'hide_title' => true,
                'description' => __('General consent banner settings.', 'rrze-legal'),
                'subsections' => [
                    [
                        'id' => 'general',
                        'title' => __('General Settings', 'rrze-legal'),
                        'description' => '',
                        'fields' => [
                            [
                                'name' => 'status',
                                'label' => __('Status', 'rrze-legal'),
                                'description' => __('Activates the Consent Banner. Displays the <strong>Banner</strong> and blocks iframes and other external media', 'rrze-legal'),
                                'type' => 'checkbox',
                                'default' => false,
                                'disabled' => consent()->hasNetworkPriority(),
                            ],
                            [
                                'name' => 'test_mode',
                                'label' => __('Test Mode', 'rrze-legal'),
                                'description' => __('Test the consent banner settings without having to enable <strong>Banner Status</strong>', 'rrze-legal'),
                                'type' => 'checkbox',
                                'default' => false,
                            ],
                            [
                                'name' => 'update_version',
                                'label' => __('Update Consent Cookie Version & Force Re-Selection', 'rrze-legal'),
                                'description' => sprintf(
                                    /* translators: %s: Consent cookie version. */
                                    __('Updates the version of the cookie of Consent Cookie. This will cause the <strong>Consent Banner</strong> to reappear for visitors who have already selected an option. <strong>Current Consent Cookie Version: %s </strong>', 'rrze-legal'),
                                    esc_html(consent()->getCookieVersion())
                                ),
                                'type' => 'checkbox',
                                'default' => false,
                            ],
                            [
                                'name' => 'cookies_for_bots',
                                'label' => __('Cookies for Bots/Crawlers', 'rrze-legal'),
                                'description' => __("A bot/crawler is treated like a visitor who accepted all cookies", 'rrze-legal'),
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => 'respect_do_not_track',
                                'label' => __('Respect "Do Not Track"', 'rrze-legal'),
                                'description' => __("A visitor with active <strong>\"Do Not Track\"</strong> setting will not see the <strong>Consent Banner</strong> and the system will automatically select the <strong>Refuse</strong> option", 'rrze-legal'),
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => 'reload_after_optout',
                                'label' => __('Reload After Opt-Out', 'rrze-legal'),
                                'description' => __("If activated the website will be reloaded after the visitor saves their consent", 'rrze-legal'),
                                'type' => 'checkbox',
                                'default' => false,
                            ],
                            [
                                'name' => 'ignore_preselected_status',
                                'label' => __('Ignore Preselected Categories', 'rrze-legal'),
                                'description' => __("If activated, no <strong>Categories</strong> are preselected in the <strong>Consent Banner</strong>", 'rrze-legal'),
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                        ],
                    ],
                    [
                        'id' => 'cookie_settings',
                        'title' => __('Cookie Settings', 'rrze-legal'),
                        'description' => __('Consent cookie settings.', 'rrze-legal'),
                        'fields' => [
                            [
                                'name' => 'domain',
                                'label' => __('Domain', 'rrze-legal'),
                                'description' => __('Specify the domain for which the cookie is valid. Example: If you enter <strong><em>example.com</em></strong> the cookie is also valid for subdomains like <strong><em>shop.example.com</em></strong>', 'rrze-legal'),
                                'type' => 'text',
                                'default' => consent()->getSiteUrlHost(),
                                'placeholder' => consent()->getSiteUrlHost(),
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                            [
                                'name' => 'path',
                                'label' => __('Path', 'rrze-legal'),
                                'description' => sprintf(
                                    /* translators: %s: Default website url path. */
                                    __('The path for which the cookie is valid. Default path: <strong>%s</strong>', 'rrze-legal'),
                                    consent()->getSiteUrlPath()
                                ),
                                'type' => 'text',
                                'default' => consent()->getSiteUrlPath(),
                                'placeholder' => consent()->getSiteUrlPath(),
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                            [
                                'name' => 'secure',
                                'label' => __('Secure', 'rrze-legal'),
                                'description' => __("Cookie is sent to the server only in case of an encrypted request via the HTTPS protocol", 'rrze-legal'),
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name'              => 'lifetime',
                                'label'             => __('Cookie Lifetime in Days', 'rrze-legal'),
                                'desc'              => __('Number of days until the visitor will be asked again to choose their cookie perference.', 'rrze-legal'),
                                'placeholder'       => '182',
                                'min'               => '30',
                                'max'               => '365',
                                'step'              => '1',
                                'type'              => 'number',
                                'default'           => '182',
                                'sanitize_callback' => function ($input) {
                                    return consent()->validateIntRange($input, 30, 365);
                                },
                            ],
                            [
                                'name'              => 'lifetime_essential_only',
                                'label'             => __('Cookie Lifetime in Days - Essential Only', 'rrze-legal'),
                                'desc'              => __('Number of days until the visitor will be asked again to choose their cookie preference, if the visitor has only given consent to essential cookies.', 'rrze-legal'),
                                'placeholder'       => '182',
                                'min'               => '30',
                                'max'               => '365',
                                'step'              => '1',
                                'type'              => 'number',
                                'default'           => '182',
                                'sanitize_callback' => function ($input) {
                                    return consent()->validateIntRange($input, 30, 365);
                                },
                            ],
                        ],
                    ],
                    [
                        'id' => 'content',
                        'title' => __('Content', 'rrze-legal'),
                        'description' => __('Configuration of the cookie banner content such as the headline, notice text and buttons text.', 'rrze-legal'),
                        'fields' => [
                            [
                                'name' => 'headline',
                                'label' => __('Headline', 'rrze-legal'),
                                'type' => 'text',
                                'default' => __('Privacy Settings', 'rrze-legal'),
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                            [
                                'name' => 'description_text',
                                'label' => __('Description Text', 'rrze-legal'),
                                'type' => 'wpeditor',
                                'default' => consent()->bannerDefaultDescription(),
                                'required' => true,
                            ],
                            [
                                'name' => 'preference_text',
                                'label' => __('Preference Text', 'rrze-legal'),
                                'type' => 'wpeditor',
                                'default' => __('Here you will find an overview of all cookies used. You can give your consent to whole categories or display further information and select certain cookies.', 'rrze-legal'),
                                'required' => true,
                            ],
                            [
                                'name' => 'accept_all_btn_txt',
                                'label' => __('Accept All Button Text', 'rrze-legal'),
                                'description' => '',
                                'type' => 'text',
                                'default' => __('Accept all', 'rrze-legal'),
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                            [
                                'name' => 'refuse_btn_txt',
                                'label' => __('Refuse Button Text', 'rrze-legal'),
                                'description' => '',
                                'type' => 'text',
                                'default' => __('Accept only essential cookies', 'rrze-legal'),
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                            [
                                'name' => 'save_btn_txt',
                                'label' => __('Save Preference Button Text', 'rrze-legal'),
                                'description' => '',
                                'type' => 'text',
                                'default' => __('Save', 'rrze-legal'),
                                'sanitize_callback' => 'sanitize_text_field',
                                'required' => true,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'content_blocker',
                'title' => __('Content Blocker', 'rrze-legal'),
                'hide_title' => true,
                'description' => __('Manage settings related to content blocking.', 'rrze-legal'),
                'capability' => apply_filters('rrze_legal_consent_capability', 'manage_options'),
                'subsections' => [
                    [
                        'id' => 'general',
                        'title' => __('General Settings', 'rrze-legal'),
                        'description' => '',
                        'fields' => [
                            [
                                'name' => 'host_whitelist',
                                'label' => __('Host(s) Allow List', 'rrze-legal'),
                                'description' => __('One host per line. When a host is recognized (for example within the src-attribute of an iframe) the content will not be blocked.', 'rrze-legal'),
                                'type' => 'textarea',
                                'default' => '',
                                'sanitize_callback' => [consent(), 'sanitizeTextareaList'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'log',
                'title' => __('Log', 'rrze-legal'),
                'hide_title' => true,
                'description' => __('Manage settings related to consents logs.', 'rrze-legal'),
                'capability' => apply_filters('rrze_legal_consent_capability', 'manage_options'),
                'subsections' => [
                    [
                        'id' => 'general',
                        'title' => __('General Settings', 'rrze-legal'),
                        'description' => '',
                        'fields' => [
                            [
                                'name' => 'active',
                                'label' => __('Activate', 'rrze-legal'),
                                'description' => __('Activate consent log', 'rrze-legal'),
                                'type' => 'checkbox',
                                'default' => false,
                            ],
                            [
                                'name' => 'purge_interval',
                                'label' => __('Purge Interval', 'rrze-legal'),
                                'description' => __('Purge consent logs after an amount of time.', 'rrze-legal'),
                                'type' => 'select',
                                'default' => '1 month',
                                'options' => [
                                    '1 month' => __('1 month', 'rrze-legal'),
                                    '3 months' => __('3 months', 'rrze-legal'),
                                ],
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
