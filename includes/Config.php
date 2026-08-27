<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

class Config {
    private array $config = [
        'accessibility_review_months' => 18,
        'tos_notice_warning_days' => 7,
        'tos_notice_error_days' => 30,
        'tos_notice_require_acknowledgement' => false,
        'tos_notice_acknowledgement_lifetime' => DAY_IN_SECONDS,
        'consent_cookie_name' => 'rrze-legal-consent',
        'consent_essential_cookie_expiry' => '+6 months',
        'theme_stylesheet_groups' => [
            'fau' => [
                'FAU-Einrichtungen',
                'FAU-Einrichtungen-BETA',
                'FAU-Medfak',
                'FAU-Philfak',
                'FAU-Natfak',
                'FAU-RWFak',
                'FAU-Techfak',
                'FAU-Jobportal',
            ],
            'rrze' => [
                'rrze-2019',
            ],
            'events' => [
                'FAU-Events',
                'FAU-Events-UTN',
                'FAU-Events UTN',
            ],
            'jobs' => [
                'fau-jobportal-theme',
            ],
            'francesca' => [
                'francesca',
                'francesca-child',
            ],
        ],
        'theme_template_filenames' => [
            'fau.php' => [
                'FAU-Einrichtungen',
                'FAU-Einrichtungen-BETA',
                'FAU-Medfak',
                'FAU-RWFak',
                'FAU-Philfak',
                'FAU-Techfak',
                'FAU-Natfak',
                'FAU-Jobportal',
            ],
            'rrze.php' => [
                'rrze-2015',
                'rrze-2019',
            ],
            'fau-elemental.php' => [
                'FAU-Elemental',
            ],
            'events.php' => [
                'FAU-Events',
                'FAU-Events-UTN',
            ],
            'jobs.php' => [
                'FAU-Jobportal-Theme',
            ],
            'francesca.php' => [
                'francesca',
                'francesca-child',
            ],
        ],
    ];

    public function get(string $key, mixed $default = null): mixed {
        return $this->config[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void {
        $this->config[$key] = $value;
    }
}
