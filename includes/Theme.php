<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

class Theme {
    /**
     * Get the current theme stylesheet.
     * @return string return the current theme stylesheet or 'default'
     */
    public static function getCurrentStylesheet()
    {
        $currentTheme = wp_get_theme();
        $currentStylesheet = $currentTheme->stylesheet;
        $parentStylesheet = !empty($currentTheme->parent()) ? $currentTheme->parent()->stylesheet : '';

        $stylesheetGroups = config()->get('theme_stylesheet_groups', []);
        foreach ($stylesheetGroups as $group => $stylesheets) {
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
