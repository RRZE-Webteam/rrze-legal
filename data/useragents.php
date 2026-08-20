<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

$data = [
    'version' => 1,
    'items' => [
        [
            'match' => 'contains',
            'value' => 'bot',
        ],
        [
            'match' => 'contains',
            'value' => 'googlebot',
        ],
        [
            'match' => 'contains',
            'value' => 'crawler',
        ],
        [
            'match' => 'contains',
            'value' => 'spider',
        ],
        [
            'match' => 'contains',
            'value' => 'robot',
        ],
        [
            'match' => 'contains',
            'value' => 'crawling',
        ],
        [
            'match' => 'contains',
            'value' => 'lighthouse',
        ],
        [
            'match' => 'contains',
            'value' => 'Siteimprove',
        ],
        [
            'match' => 'contains',
            'value' => 'RRZE CheckBot',
        ],
        [
            'match' => 'starts_with',
            'value' => 'FAU-',
        ],
        [
            'match' => 'starts_with',
            'value' => 'RRZE',
        ],
    ],
];
