<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

$data = [
    'version' => 1,
    'items' => [
        'essential' => [
            'id' => 'essential',
            'name' => __('Essential', 'rrze-legal'),
            'description' => __('Essential cookies enable basic functions and are necessary for the proper function of the website.', 'rrze-legal'),
            'preselected' => true,
            'position' => 1,
            'status' => true,
            'static' => true,
        ],
        'statistics' => [
            'id' => 'statistics',
            'name' => __('Statistics', 'rrze-legal'),
            'description' => __('Statistics cookies collect information anonymously. This information helps us to understand how our visitors use our website.', 'rrze-legal'),
            'preselected' => false,
            'position' => 2,
            'status' => true,
            'static' => true,
        ],
        'marketing' => [
            'id' => 'marketing',
            'name' => __('Marketing', 'rrze-legal'),
            'description' => __('Marketing cookies are used by third-party advertisers or publishers to display personalized ads. They do this by tracking visitors across websites.', 'rrze-legal'),
            'preselected' => false,
            'position' => 3,
            'status' => true,
            'static' => true,
        ],
        'external_media' => [
            'id' => 'external_media',
            'name' => __('External Media', 'rrze-legal'),
            'description' => __('Content from video platforms and social media platforms is blocked by default. If External Media cookies are accepted, access to those contents no longer requires manual consent.', 'rrze-legal'),
            'preselected' => false,
            'position' => 4,
            'status' => true,
            'static' => true,
        ],
    ],
];
