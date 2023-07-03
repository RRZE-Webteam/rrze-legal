<?php

/*
Plugin Name:     RRZE Legal
Plugin URI:      https://gitlab.rrze.fau.de/rrze-webteam/rrze-legal
Description:     Legal Mandatory Information & GDPR.
Version:         2.6.15
Author:          RRZE Webteam
Author URI:      https://blogs.fau.de/webworking/
License:         GNU General Public License Version 3
License URI:     https://www.gnu.org/licenses/gpl-3.0.html
Domain Path:     /languages
Text Domain:     rrze-legal
*/

namespace RRZE\Legal;

defined('ABSPATH') || exit;

use RRZE\Legal\Network\Options as NetworkOptions;
use RRZE\Legal\TOS\Options as TOSOptions;
use RRZE\Legal\Consent\Options as ConsentOptions;
use RRZE\Legal\Consent\Categories\Options as ConsentCategoriesOptions;
use RRZE\Legal\Consent\Cookies\Options as ConsentCookiesOptions;

const RRZE_PHP_VERSION = '8.0';
const RRZE_WP_VERSION  = '6.2';

/**
 * SPL Autoloader (PSR-4).
 * @param string $class The fully-qualified class name.
 * @return void
 */
spl_autoload_register(function ($class) {
    $prefix = __NAMESPACE__;
    $baseDir = __DIR__ . '/includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Register plugin hooks.
register_activation_hook(__FILE__, __NAMESPACE__ . '\activation');
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\deactivation');

add_action('plugins_loaded', __NAMESPACE__ . '\loaded');

/**
 * Loads a plugin’s translated strings.
 */
function loadTextdomain()
{
    load_plugin_textdomain('rrze-legal', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

/**
 * System requirements verification.
 * @return string Return an error message.
 */
function systemRequirements(): string
{
    $error = '';
    if (version_compare(PHP_VERSION, RRZE_PHP_VERSION, '<')) {
        $error = sprintf(
            /* translators: 1: Server PHP version number, 2: Required PHP version number. */
            __('The server is running PHP version %1$s. The Plugin requires at least PHP version %2$s.', 'rrze-legal'),
            PHP_VERSION,
            RRZE_PHP_VERSION
        );
    } elseif (version_compare($GLOBALS['wp_version'], RRZE_WP_VERSION, '<')) {
        $error = sprintf(
            /* translators: 1: Server WordPress version number, 2: Required WordPress version number. */
            __('The server is running WordPress version %1$s. The Plugin requires at least WordPress version %2$s.', 'rrze-legal'),
            $GLOBALS['wp_version'],
            RRZE_WP_VERSION
        );
    }
    return $error;
}

/**
 * Activation callback function.
 */
function activation()
{
    loadTextdomain();
    if ($error = systemRequirements()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            sprintf(
                /* translators: 1: The plugin name, 2: The error string. */
                __('Plugins: %1$s: %2$s', 'rrze-legal'),
                plugin_basename(__FILE__),
                $error
            )
        );
    }
}

/**
 * Deactivation callback function.
 */
function deactivation()
{
    flush_rewrite_rules();
}

/**
 * Instantiate Plugin class.
 * @return object Plugin
 */
function plugin()
{
    static $instance;
    if (null === $instance) {
        $instance = new Plugin(__FILE__);
    }

    return $instance;
}

/**
 * Instantiate Network Options class.
 * @return object Plugin
 */
function network()
{
    static $instance;
    if (null === $instance) {
        $instance = new NetworkOptions();
    }
    return $instance;
}

/**
 * Instantiate TOS Options class.
 * @return object Plugin
 */
function tos()
{
    static $instance;
    if (null === $instance) {
        $instance = new TOSOptions();
    }
    return $instance;
}

/**
 * Instantiate Consent Options class.
 * @return object Plugin
 */
function consent()
{
    static $instance;
    if (null === $instance) {
        $instance = new ConsentOptions();
    }
    return $instance;
}

/**
 * Instantiate Consent Categories Options class.
 * @return object Plugin
 */
function consentCategories()
{
    static $instance;
    if (null === $instance) {
        $instance = new ConsentCategoriesOptions();
    }
    return $instance;
}

/**
 * Instantiate Consent Cookies Options class.
 * @return object Plugin
 */
function consentCookies()
{
    static $instance;
    if (null === $instance) {
        $instance = new ConsentCookiesOptions();
    }
    return $instance;
}

/**
 * TOS Plugin Deactivation.
 */
function tosPluginDeactivation()
{
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
    if (is_plugin_active('rrze-tos/rrze-tos.php')) {
        deactivate_plugins('rrze-tos/rrze-tos.php');
    }
    if (is_plugin_active_for_network('rrze-tos/rrze-tos.php')) {
        add_action('network_admin_notices', function () {
            echo '<div class="notice notice-warning"><p>',
            __('The "rrze-tos" plugin is networkwide activated. Please deactivate the "rrze-tos" plugin as it will be replaced by the "rrze-legal" plugin.', 'rrze-legal'),
            '</p></div>';
        });
        return false;
    }
    return true;
}

/**
 * Execute on 'plugins_loaded' API/action.
 * @return void
 */
function loaded()
{
    if (!tosPluginDeactivation()) {
        return;
    }
    loadTextdomain();
    plugin()->loaded();
    if ($error = systemRequirements()) {
        add_action('admin_init', function () use ($error) {
            if (current_user_can('activate_plugins')) {
                $pluginData = get_plugin_data(plugin()->getFile());
                $pluginName = $pluginData['Name'];
                $tag = is_plugin_active_for_network(plugin()->getBaseName()) ? 'network_admin_notices' : 'admin_notices';
                add_action($tag, function () use ($pluginName, $error) {
                    printf(
                        '<div class="notice notice-error"><p>' .
                            /* translators: 1: The plugin name, 2: The error string. */
                            __('Plugins: %1$s: %2$s', 'rrze-legal') .
                            '</p></div>',
                        esc_html($pluginName),
                        esc_html($error)
                    );
                });
            }
        });
        return;
    }
    new Main;
}
