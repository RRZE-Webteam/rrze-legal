<?php

namespace RRZE\Legal\Tos;

defined('ABSPATH') || exit;

use RRZE\Legal\Theme;

class NavMenu
{
    /**
     * Menu slug.
     */
    const TOS_MENU_SLUG = 'rrze-tos-menu';

    /**
     * Get the menu location.
     * @return array
     */
    protected static function menuLocations()
    {
        return [
            'fau' => 'meta-footer',
        ];
    }

    /**
     * Add the TOS menu.
     */
    public static function addTosMenu()
    {
        add_action('init', [__CLASS__, 'setTosMenu']);
    }

    /**
     * [setTosMenu description]
     */
    public static function setTosMenu()
    {
        if (!is_nav_menu(self::TOS_MENU_SLUG)) {
            self::createTosMenu();
        }
    }

    /**
     * Create the Menu-Items for the TOS-Menu.
     */
    public static function createMenuItems()
    {
        $slugs = Endpoint::slugsTitles();
        $menulist = [];
        foreach ($slugs as $slug => $title) {
            $menulist[$slug] = $title;
        }
        return $menulist;
    }
    /**
     * Set the Nav Menu.
     */
    protected static function createTosMenu()
    {
        $menuLocations = self::menuLocations();
        $stylesheet = Theme::getCurrentStylesheet();

        $menuItems = self::createMenuItems();
        $menuName  = self::TOS_MENU_SLUG;
        $menuLocation = isset($menuLocations[$stylesheet]) ? $menuLocations[$stylesheet] : '';

        self::createNavMenu($menuName, $menuItems, $menuLocation);
    }

    /**
     * Create the Nav Menu.
     * @param  string  $menuName
     * @param  array  $menuItems
     * @param  string  $menuLocation 
     * @return mixed
     */
    protected static function createNavMenu($menuName, $menuItems, $menuLocation = '')
    {
        if (is_nav_menu($menuName)) {
            return null;
        }

        $menuId = wp_create_nav_menu($menuName);
        if (is_wp_error($menuId)) {
            return $menuId; // return an instance of \WP_Error.
        }

        $menu = get_term_by('name', $menuName, 'nav_menu');

        foreach ($menuItems as $slug => $value) {
            wp_update_nav_menu_item(
                $menu->term_id,
                0,
                [
                    'menu-item-title'   => mb_convert_case($value, MB_CASE_TITLE, 'UTF-8'),
                    'menu-item-classes' => 'tos',
                    'menu-item-url'     => home_url('/' . sanitize_title($slug)),
                    'menu-item-status'  => 'publish',
                ]
            );
        }

        if ($menuLocation) {
            $locations = get_theme_mod('nav_menu_locations');
            $locations[$menuLocation] = $menu->term_id;
            set_theme_mod('nav_menu_locations', $locations);
        }

        return $menuId;
    }
}
