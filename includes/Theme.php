<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

class Theme
{
    /**
     * Stylesheets Groups
     * @var array
     */
    protected static $stylesheetsGroups =  [
        'fau' => [
            'FAU-Einrichtungen',
            'FAU-Einrichtungen-BETA',
            'FAU-Medfak',
            'FAU-Philfak',
            'FAU-Natfak',
            'FAU-RWFak',
            'FAU-Techfak',
            'FAU-Jobportal'
        ],
        'rrze' => [
            'rrze-2019',
        ],
        'events' => [
            'FAU-Events',
        ],
        'jobs' => [
            'fau-jobportal-theme',
        ]
    ];

    /**
     * Get the current theme stylesheet.
     * @return string return the current theme stylesheet or 'default'
     */
    public static function getCurrentStylesheet()
    {
        $currentTheme = wp_get_theme();
        $currentStylesheet = $currentTheme->stylesheet;
        $parentStylesheet = !empty($currentTheme->parent()) ? $currentTheme->parent()->stylesheet : '';

        foreach (self::$stylesheetsGroups as $group => $stylesheets) {
            if (in_array(
                strtolower($currentStylesheet),
                array_map('strtolower', $stylesheets),
                true
            )) {
                return $group;
            }
            if (in_array(
                strtolower($parentStylesheet),
                array_map('strtolower', $stylesheets),
                true
            )) {
                return $group;
            }
        }

        return 'default';
    }
}
