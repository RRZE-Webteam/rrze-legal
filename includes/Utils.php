<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

class Utils
{
    public static function getHostname()
    {
        return parse_url(get_site_url(), PHP_URL_HOST);
    }

    public static function getFAUDomains()
    {
        return ['fau.de', 'fau.eu', 'uni-erlangen.de'];
    }

    public static function isPluginActive(string $plugin)
    {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        return (is_plugin_active_for_network($plugin) || is_plugin_active($plugin));
    }

    public static function redirectToReferer()
    {
        if (wp_get_referer()) {
            wp_safe_redirect(wp_get_referer());
        } else {
            wp_safe_redirect(site_url());
        }
        exit;
    }

    public static function debug($input, string $level = 'i')
    {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        if (in_array(strtolower((string) WP_DEBUG_LOG), ['true', '1'], true)) {
            $logPath = WP_CONTENT_DIR . '/debug.log';
        } elseif (is_string(WP_DEBUG_LOG)) {
            $logPath = WP_DEBUG_LOG;
        } else {
            return;
        }
        if (is_array($input) || is_object($input)) {
            $input = print_r($input, true);
        }
        switch (strtolower($level)) {
            case 'e':
            case 'error':
                $level = 'Error';
                break;
            case 'i':
            case 'info':
                $level = 'Info';
                break;
            case 'd':
            case 'debug':
                $level = 'Debug';
                break;
            default:
                $level = 'Info';
        }
        error_log(
            date("[d-M-Y H:i:s \U\T\C]")
                . " WP $level: "
                . basename(__FILE__) . ' '
                . $input
                . PHP_EOL,
            3,
            $logPath
        );
    }
}
