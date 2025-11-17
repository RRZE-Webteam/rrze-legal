<?php

/*
Plugin Name:        RRZE Legal
Plugin URI:         https://gitlab.rrze.fau.de/rrze-webteam/rrze-legal
Version:            2.8.5
Description:        Legal Mandatory Information & GDPR.
Author:             RRZE Webteam
Author URI:         https://www.rrze.fau.de
License:            GNU General Public License Version 3
License URI:        https://www.gnu.org/licenses/gpl-3.0.html
Text Domain:        rrze-legal
Domain Path:        /languages
Requires at least:  6.7
Requires PHP:       8.2
*/

namespace RRZE\Legal;

defined('ABSPATH') || exit;

use RRZE\Legal\Network\Options as NetworkOptions;
use RRZE\Legal\TOS\Options as TOSOptions;
use RRZE\Legal\Consent\Options as ConsentOptions;
use RRZE\Legal\Consent\Categories\Options as ConsentCategoriesOptions;
use RRZE\Legal\Consent\Cookies\Options as ConsentCookiesOptions;

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

// Load the plugin's text domain for localization.
add_action('init', fn() => load_plugin_textdomain('rrze-legal', false, dirname(plugin_basename(__FILE__)) . '/languages'));


// Register activation hook for the plugin
register_activation_hook(__FILE__, __NAMESPACE__ . '\activation');

// Register deactivation hook for the plugin
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\deactivation');

/**
 * Add an action hook for the 'plugins_loaded' hook.
 *
 * This code hooks into the 'plugins_loaded' action hook to execute a callback function when
 * WordPress has fully loaded all active plugins and the theme's functions.php file.
 */
add_action('plugins_loaded', __NAMESPACE__ . '\loaded');

/**
 * Activation callback function.
 */
function activation()
{
    //
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
            esc_html(__('The "rrze-tos" plugin is networkwide activated. Please deactivate the "rrze-tos" plugin as it will be replaced by the "rrze-legal" plugin.', 'rrze-legal'), 'post'),
            '</p></div>';
        });
        return false;
    }
    return true;
}

/**
 * Get FAU domains.
 * @return array
 */
function fauDomains(): array
{
    return apply_filters(
        'rrze_legal_fau_domains',
        [
            'fau.de',
            'fau.eu',
            'uni-erlangen.de'
        ]
    );
}

/**
 * Check system requirements for the plugin.
 *
 * This method checks if the server environment meets the minimum WordPress and PHP version requirements
 * for the plugin to function properly.
 *
 * @return string An error message string if requirements are not met, or an empty string if requirements are satisfied.
 */
function systemRequirements(): string
{
    // Get the global WordPress version.
    global $wp_version;

    // Get the PHP version.
    $phpVersion = phpversion();

    // Initialize an error message string.
    $error = '';

    // Check if the WordPress version is compatible with the plugin's requirement.
    if (!is_wp_version_compatible(plugin()->getRequiresWP())) {
        $error = sprintf(
            /* translators: 1: Server WordPress version number, 2: Required WordPress version number. */
            __('The server is running WordPress version %1$s. The plugin requires at least WordPress version %2$s.', 'rrze-legal'),
            $wp_version,
            plugin()->getRequiresWP()
        );
    } elseif (!is_php_version_compatible(plugin()->getRequiresPHP())) {
        // Check if the PHP version is compatible with the plugin's requirement.
        $error = sprintf(
            /* translators: 1: Server PHP version number, 2: Required PHP version number. */
            __('The server is running PHP version %1$s. The plugin requires at least PHP version %2$s.', 'rrze-legal'),
            $phpVersion,
            plugin()->getRequiresPHP()
        );
    }

    // Return the error message string, which will be empty if requirements are satisfied.
    return $error;
}

/**
 * Handle the loading of the plugin.
 *
 * This function is responsible for initializing the plugin, loading text domains for localization,
 * checking system requirements, and displaying error notices if necessary.
 */
function loaded()
{
    if (!tosPluginDeactivation()) {
        return;
    }
    // Trigger the 'loaded' method of the main plugin instance.
    plugin()->loaded();
    // Check system requirements.
    if (systemRequirements()) {
        // If there is an error, add an action to display an admin notice with the error message.
        add_action('admin_init', function () {
            $error = systemRequirements();
            // Check if the current user has the capability to activate plugins.
            if (current_user_can('activate_plugins')) {
                // Get plugin data to retrieve the plugin's name.
                $pluginName = plugin()->getName();

                // Determine the admin notice tag based on network-wide activation.
                $tag = is_plugin_active_for_network(plugin()->getBaseName()) ? 'network_admin_notices' : 'admin_notices';

                // Add an action to display the admin notice.
                add_action($tag, function () use ($pluginName, $error) {
                    printf(
                        '<div class="notice notice-error"><p>' .
                            /* translators: 1: The plugin name, 2: The error string. */
                            esc_html__('Plugins: %1$s: %2$s', 'rrze-legal') .
                            '</p></div>',
                        $pluginName,
                        $error
                    );
                });
            }
        });

        // Return to prevent further initialization if there is an error.
        return;
    }

    // If there are no errors, create an instance of the 'Main' class
    new Main;
}
