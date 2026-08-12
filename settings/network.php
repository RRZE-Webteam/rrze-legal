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
                'description' => '',
                'subsections' => [
                    [
                        'id' => 'endpoints',
                        'title' => __('Endpoints', 'rrze-legal'),
                        'description' => '',
                        'fields' => [
                            [
                                'name' => 'overwrite_endpoints',
                                'label' => __('Overwrite Endpoints', 'rrze-legal'),
                                'description' => __('Websites may create their own, manually created web pages for the legal text. These then have priority over the automatically created texts and are shown instead', 'rrze-legal'),
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => 'exceptions',
                                'label' => __('Exceptions', 'rrze-legal'),
                                'description' => __('List of website IDs that are exempt from the network settings if websites are not allowed to override endpoints. Enter one website ID per line.', 'rrze-legal'),
                                'type' => 'textarea',
                                'default' => '',
                                'disabled' => network()->isOverwriteEndpointsEnabled(),
                                'sanitize_callback' => [network(), 'sanitizeTextareaSitesList'],
                            ],
                        ],
                    ],
                    [
                        'id' => 'organizational_assignments',
                        'title' => __('Organizational Assignments', 'rrze-legal'),
                        'description' => __('Assign domains or unique domain parts to the organizations from the site setting "Organizational Affiliation".', 'rrze-legal'),
                        'fields' => network()->getOrganizationDomainFields(),
                    ],
                    [
                        'id' => 'tos_notices',
                        'title' => __('TOS Data Issue Notices', 'rrze-legal'),
                        'description' => __('Configuration of notices and log messages for missing or incorrectly completed legal text data.', 'rrze-legal'),
                        'fields' => [
                            [
                                'name' => 'tos_notice_warning_days',
                                'label' => __('Warning after days', 'rrze-legal'),
                                'description' => __('Number of days after the first notice before a warning is written to rrze.log.warning and an additional notice is displayed.', 'rrze-legal'),
                                'type' => 'number',
                                'default' => 7,
                                'min' => 1,
                                'step' => 1,
                                'sanitize_callback' => [network(), 'sanitizePositiveInteger'],
                            ],
                            [
                                'name' => 'tos_notice_error_days',
                                'label' => __('Error message after days', 'rrze-legal'),
                                'description' => __('Number of days after the first notice before a message is written to rrze.log.error and an additional notice is displayed.', 'rrze-legal'),
                                'type' => 'number',
                                'default' => 30,
                                'min' => 1,
                                'step' => 1,
                                'sanitize_callback' => [network(), 'sanitizePositiveInteger'],
                            ],
                            [
                                'name' => 'tos_notice_warning_text',
                                'label' => __('Notice after warning period', 'rrze-legal'),
                                'description' => __('Text displayed in the admin notice when the warning period has been exceeded.', 'rrze-legal'),
                                'type' => 'textarea',
                                'default' => __('Continuing to leave the data unprocessed will result in a report to the CMS operator.', 'rrze-legal'),
                                'sanitize_callback' => 'sanitize_textarea_field',
                            ],
                            [
                                'name' => 'tos_notice_error_text',
                                'label' => __('Notice after error period', 'rrze-legal'),
                                'description' => __('Text displayed in the admin notice when the error period has been exceeded.', 'rrze-legal'),
                                'type' => 'textarea',
                                'default' => __('The CMS operator has been informed.', 'rrze-legal'),
                                'sanitize_callback' => 'sanitize_textarea_field',
                            ],
                            [
                                'name' => 'tos_notice_require_acknowledgement',
                                'label' => __('Require confirmation after error period', 'rrze-legal'),
                                'description' => __('If enabled, backend users must confirm the notice by dialog and checkbox after the error period has been reached before they can continue working.', 'rrze-legal'),
                                'type' => 'checkbox',
                                'default' => false,
                            ],
                        ],
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
                    [
                        'name' => 'cookies_for_ip_addresses',
                        'label' => __('Cookies For IP Addresses', 'rrze-legal'),
                        'description' => __('These IP addresses are treated as a visitor who accepted all cookies. Add one IP address per line.', 'rrze-legal'),
                        'type' => 'textarea',
                        'default' => '',
                        'sanitize_callback' => [network(), 'sanitizeTextareaIpList'],
                    ],
                ],
            ],
        ],
    ],
];
