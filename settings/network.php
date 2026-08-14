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
                                'description' => __('These global IP addresses are treated as visitors who accepted all cookies on all websites in the network. Add one IP address per line.', 'rrze-legal'),
                                'type' => 'textarea',
                                'default' => '',
                                'sanitize_callback' => [network(), 'sanitizeTextareaIpList'],
                            ],
                            [
                                'name' => 'cookies_for_bots',
                                'label' => __('Cookies for Bots/Crawlers', 'rrze-legal'),
                                'description' => __("A bot/crawler is treated like a visitor who accepted all cookies", 'rrze-legal'),
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => 'cookies_for_user_agents',
                                'label' => __('User-Agent Strings for Bots/Crawlers', 'rrze-legal'),
                                'description' => consent()->getDefaultUserAgentPatternsDescription() . '<p>' . __('Additional global User-Agent strings. If one of these strings occurs in the User-Agent header, the visitor is treated like a visitor who accepted all cookies. Add one string per line.', 'rrze-legal') . '</p>',
                                'type' => 'textarea',
                                'default' => '',
                                'readonly' => !(bool) network()->getOption('network_banner', 'cookies_for_bots', true),
                                'sanitize_callback' => [network(), 'sanitizeTextareaList'],
                            ],
                        ],
                    ],
                    [
                        'id' => 'cookie_settings',
                        'title' => __('Cookie Settings', 'rrze-legal'),
                        'description' => __('Consent cookie settings.', 'rrze-legal'),
                        'fields' => [
                            [
                                'name' => 'secure',
                                'label' => __('Secure', 'rrze-legal'),
                                'description' => __("Cookie is sent to the server only in case of an encrypted request via the HTTPS protocol", 'rrze-legal'),
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => 'lifetime',
                                'label' => __('Cookie Lifetime in Days', 'rrze-legal'),
                                'desc' => __('Number of days until the visitor will be asked again to choose their cookie perference.', 'rrze-legal'),
                                'placeholder' => '182',
                                'min' => '30',
                                'max' => '365',
                                'step' => '1',
                                'type' => 'number',
                                'default' => '182',
                                'sanitize_callback' => function ($input) {
                                    return network()->validateIntRange($input, 30, 365);
                                },
                            ],
                            [
                                'name' => 'lifetime_essential_only',
                                'label' => __('Cookie Lifetime in Days - Essential Only', 'rrze-legal'),
                                'desc' => __('Number of days until the visitor will be asked again to choose their cookie preference, if the visitor has only given consent to essential cookies.', 'rrze-legal'),
                                'placeholder' => '182',
                                'min' => '30',
                                'max' => '365',
                                'step' => '1',
                                'type' => 'number',
                                'default' => '182',
                                'sanitize_callback' => function ($input) {
                                    return network()->validateIntRange($input, 30, 365);
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
                    [
                        'id' => 'content_blocker',
                        'title' => __('Content Blocker', 'rrze-legal'),
                        'description' => __('Manage settings related to content blocking.', 'rrze-legal'),
                        'fields' => [
                            [
                                'name' => 'content_blocker_host_whitelist',
                                'label' => __('Host(s) Allow List', 'rrze-legal'),
                                'description' => __('One host per line. When a host is recognized (for example within the src-attribute of an iframe) the content will not be blocked.', 'rrze-legal'),
                                'type' => 'textarea',
                                'default' => '',
                                'sanitize_callback' => [network(), 'sanitizeTextareaList'],
                            ],
                        ],
                    ],
                    [
                        'id' => 'log',
                        'title' => __('Log', 'rrze-legal'),
                        'description' => __('Manage settings related to consents logs.', 'rrze-legal'),
                        'fields' => [
                            [
                                'name' => 'log_active',
                                'label' => __('Activate', 'rrze-legal'),
                                'description' => __('Activate consent log', 'rrze-legal'),
                                'type' => 'checkbox',
                                'default' => false,
                            ],
                            [
                                'name' => 'log_purge_interval',
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
