<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

class Theme
{
    /**
     * FAU Stylesheets
     * @var array
     */
    protected static $fauStylesheets =  [
        'fau' => [
            'FAU-Einrichtungen',
            'FAU-Einrichtungen-BETA',
            'FAU-Medfak',
            'FAU-RWFak',
            'FAU-Philfak',
            'FAU-Techfak',
            'FAU-Natfak',
            'FAU-Jobportal'
        ]
    ];

    /**
     * Color Stylesheets
     * @var array
     */
    protected static $colorStylesheets = [
        'phil' => [
            'FAU-Philfak',
        ],
        'rw' => [
            'FAU-RWFak',
        ],
        'med' => [
            'FAU-Medfak',
        ],
        'nat' => [
            'FAU-Natfak',
        ],
        'tf' => [
            'FAU-Techfak',
        ]
    ];

    /**
     * FAU Colors
     * @var array
     */
    protected static $colors = [
        'default' => '#041E42', // FAU
        'phil'    => '#963B2F', // Phil
        'rw'      => '#662938', // RW
        'med'     => '#003E61', // Med
        'nat'     => '#14462D', // Nat
        'tf'      => '#204251'  // TF
    ];

    /**
     * Get the Theme color
     * @var string
     */
    public static function getThemeColor(): string
    {
        $currentTheme = wp_get_theme();
        foreach (self::$colorStylesheets as $key => $theme) {
            if (in_array(strtolower($currentTheme->stylesheet), array_map('strtolower', $theme))) {
                return self::$colors[$key];
            }
        }
        return self::$colors['default'];
    }

    /**
     * Get the FAU Stylesheets.
     * @var string
     */
    public static function getFauStylesheets(): array
    {
        return self::$fauStylesheets;
    }
}
