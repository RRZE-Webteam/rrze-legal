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
                        'title' => __('Organisatorische Zuordnungen', 'rrze-legal'),
                        'description' => __('Ordnen Sie Domains oder eindeutige Domainteile den Organisationen aus der Site-Einstellung "Organisatorische Zugehörigkeit" zu.', 'rrze-legal'),
                        'fields' => network()->getOrganizationDomainFields(),
                    ],
                    [
                        'id' => 'tos_notices',
                        'title' => __('TOS-Mängelmeldungen', 'rrze-legal'),
                        'description' => __('Konfiguration der Hinweise und Logmeldungen bei fehlenden oder nicht korrekt ausgefüllten Rechtstext-Daten.', 'rrze-legal'),
                        'fields' => [
                            [
                                'name' => 'tos_notice_warning_days',
                                'label' => __('Warnung nach Tagen', 'rrze-legal'),
                                'description' => __('Anzahl der Tage seit der ersten Meldung, nach denen eine Warnung in rrze.log.warning geschrieben und ein zusätzlicher Hinweis angezeigt wird.', 'rrze-legal'),
                                'type' => 'number',
                                'default' => 7,
                                'min' => 1,
                                'step' => 1,
                                'sanitize_callback' => [network(), 'sanitizePositiveInteger'],
                            ],
                            [
                                'name' => 'tos_notice_error_days',
                                'label' => __('Fehlermeldung nach Tagen', 'rrze-legal'),
                                'description' => __('Anzahl der Tage seit der ersten Meldung, nach denen eine Meldung in rrze.log.error geschrieben und ein zusätzlicher Hinweis angezeigt wird.', 'rrze-legal'),
                                'type' => 'number',
                                'default' => 30,
                                'min' => 1,
                                'step' => 1,
                                'sanitize_callback' => [network(), 'sanitizePositiveInteger'],
                            ],
                            [
                                'name' => 'tos_notice_warning_text',
                                'label' => __('Hinweis nach Warnfrist', 'rrze-legal'),
                                'description' => __('Text, der in der Admin-Notice angezeigt wird, wenn die Warnfrist überschritten ist.', 'rrze-legal'),
                                'type' => 'textarea',
                                'default' => __('Eine weitergehende Nichtbearbeitung der Daten führt zu einer Meldung beim CMS Betreiber.', 'rrze-legal'),
                                'sanitize_callback' => 'sanitize_textarea_field',
                            ],
                            [
                                'name' => 'tos_notice_error_text',
                                'label' => __('Hinweis nach Fehlerfrist', 'rrze-legal'),
                                'description' => __('Text, der in der Admin-Notice angezeigt wird, wenn die Fehlerfrist überschritten ist.', 'rrze-legal'),
                                'type' => 'textarea',
                                'default' => __('Der CMS Betreiber wurde informiert.', 'rrze-legal'),
                                'sanitize_callback' => 'sanitize_textarea_field',
                            ],
                            [
                                'name' => 'tos_notice_require_acknowledgement',
                                'label' => __('Bestätigung nach Fehlerfrist erzwingen', 'rrze-legal'),
                                'description' => __('Wenn aktiviert, müssen Backend-Benutzer die Meldung nach Erreichen der Fehlerfrist per Dialog und Checkbox bestätigen, bevor sie weiterarbeiten können.', 'rrze-legal'),
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
