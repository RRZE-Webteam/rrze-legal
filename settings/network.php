<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

$settings = [
    'version' => 1,
    'options_page' => [
        'parent' => [
            'slug' => 'settings.php',
        ],
        'page' => [
            'title' => __('Legal', 'rrze-legal'),
        ],
        'menu' => [
            'title' => __('Legal', 'rrze-legal'),
            'capability' => 'manage_network_options',
            'slug' => 'legal',
            'position' => 99,
        ],
    ],
    'settings' => [
        'title' => __('Network Legal Settings', 'rrze-legal'),
        'sections' => [
            [
                'id' => 'network_general',
                'title' => __('General', 'rrze-legal'),
                'hide_title' => true,
                'description' => __('General network settings.', 'rrze-legal'),
                'fields' => [
                    [
                        'name' => 'fau_domains',
                        'label' => __('FAU domains', 'rrze-legal'),
                        'description' => __('Websites belonging to FAU domains will use the default FAU data for some sections.', 'rrze-legal'),
                        'type' => 'textarea',
                        'default' => tos()->getDefaultDomainsToString(),
                        'sanitize_callback' => [network(), 'sanitizeTextareaList'],
                    ],
                    [
                        'name' => 'exceptions',
                        'label' => __('Exceptions', 'rrze-legal'),
                        'description' => __('List of website IDs that are exempt from the network settings. Enter one website ID per line.', 'rrze-legal'),
                        'type' => 'textarea',
                        'default' => '',
                        'sanitize_callback' => [network(), 'sanitizeTextareaSitesList'],
                    ],
                ],
            ],
            [
                'id' => 'network_banner',
                'title' => __('Banner', 'rrze-legal'),
                'hide_title' => true,
                'description' => __('General network settings for consent banner.', 'rrze-legal'),
                'fields' => [
                    [
                        'name' => 'status',
                        'label' => __('Status', 'rrze-legal'),
                        'description' => __('Activates the Consent Banner. Displays the <strong>Banner</strong> and blocks iframes and other external media', 'rrze-legal'),
                        'type' => 'checkbox',
                        'default' => false,
                    ],
                    [
                        'name' => 'update_version',
                        'label' => __('Update Consent Cookie Version & Force Re-Selection', 'rrze-legal'),
                        'description' => sprintf(
                            /* translators: %s: Consent cookie version. */
                            __('Updates the version of the cookie of Consent Cookie. This will cause the <strong>Consent Banner</strong> to reappear for visitors who have already selected an option. <strong>Current Consent Cookie Version: %s </strong>', 'rrze-legal'),
                            esc_html(network()->getCookieVersion())
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
        ],
    ],
];
