<?php

namespace RRZE\Legal\Consent;

defined('ABSPATH') || exit;

use RRZE\Legal\Utils;
use function RRZE\Legal\{plugin, consent};
use const RRZE\PrivateSite\PRIVATE_SITE_OPTION;

class Frontend
{
    public static function loaded()
    {
        add_action('init', [__CLASS__, 'init']);

        // Register handler for AJAX requests
        add_action('wp_ajax_banner_log_handler', [__CLASS__, 'handleLogAjaxRequest']);
        add_action('wp_ajax_nopriv_banner_log_handler', [__CLASS__, 'handleLogAjaxRequest']);
        add_action('wp_ajax_banner_cookies_for_ip_addresses_handler', [__CLASS__, 'handleCookiesForIpAddresses']);
        add_action('wp_ajax_nopriv_banner_cookies_for_ip_addresses_handler', [__CLASS__, 'handleCookiesForIpAddresses']);
    }

    /**
     * init function.
     */
    public static function init()
    {
        if (self::rrzeAccessControlPlugin()) {
            Cookies::setEssentialCookie();
            return;
        } elseif (self::rrzePrivateSitePlugin()) {
            return;
        }

        if (consent()->isBannerActive() || consent()->isTestModeActive()) {
            // Add scripts and styles
            add_action('wp_enqueue_scripts', [__CLASS__, 'enqueueScripts']);
            add_action('wp_head', [__CLASS__, 'head']);
            add_action('wp_footer', [__CLASS__, 'footer']);

            // Block scripts
            add_action('template_redirect', [Buffer::getInstance(), 'handleBuffering'], 9998);
            add_filter('script_loader_tag', [ScriptBlocker::getInstance(), 'blockHandles'], 999, 3);
            add_action('wp_footer', [ScriptBlocker::getInstance(), 'handleJavaScriptTagBlocking'], 9998);

            // Add banner
            add_action('wp_footer', [__CLASS__, 'addBanner']);

            // Register shortcodes
            add_shortcode('rrzelegal_consent', [__CLASS__, 'handleShortcode']);

            // Block cookies
            add_action('wp', [__CLASS__, 'handleCookieBlocking']);

            // Block content
            add_filter('the_content', [__CLASS__, 'handleContentBlocking'], 100, 1);
            add_filter('embed_oembed_html', [__CLASS__, 'handleOembedBlocking'], 100, 2);
            add_filter('widget_custom_html_content', [__CLASS__, 'handleContentBlocking'], 100, 1);
            add_filter('widget_text_content', [__CLASS__, 'handleContentBlocking'], 100, 1);
            add_filter('widget_block_content', [__CLASS__, 'handleContentBlocking'], 100, 1);
            add_filter('rrze_video_player_content', [__CLASS__, 'handleRRZEVideoBlocking'], 100, 1);
        }
    }

    public static function enqueueScripts()
    {
        wp_enqueue_style(
            'rrze-legal-cookie',
            plugins_url('build/banner.css', plugin()->getBasename()),
            [],
            plugin()->getVersion()
        );
        JavaScript::instance()->registerHead();
    }

    public static function head()
    {
        JavaScript::instance()->registerHeadFallback();
    }

    public static function footer()
    {
        JavaScript::instance()->registerFooter();
    }

    public static function addBanner()
    {
        Banner::add();
    }

    public static function handleShortcode($atts, $content = '')
    {
        return Shortcode::handleShortcode($atts, $content);
    }

    public static function handleCookieBlocking()
    {
        CookieBlocker::handleBlocking();
    }

    public static function handleContentBlocking($content)
    {
        return ContentBlocker::instance()->detectIframes($content);
    }

    public static function handleOembedBlocking($html, $url)
    {
        return ContentBlocker::instance()->handleOembed($html, $url);
    }

    public static function handleRRZEVideoBlocking($content)
    {
        return ContentBlocker::instance()->handleRRZEVideo($content);
    }

    /**
     * Handle Log (ajax request).
     */
    public static function handleLogAjaxRequest()
    {
        if (!empty($_POST['type'])) {
            $requestType = $_POST['type'];

            // Frontend request
            if ($requestType == 'log' && !empty($_POST['cookieData'])) {
                echo json_encode([
                    'success' => Log::add($_POST['cookieData']),
                ]);
            } elseif ($requestType == 'consent_history' && !empty($_POST['uid'])) {
                echo json_encode(Log::getConsentHistory($_POST['uid']));
            }
        }
        wp_die();
    }

    /**
     * Handle Hide On IP Address (ajax request).
     */
    public static function handleCookiesForIpAddresses()
    {
        $ipAddresses = consent()->getCookiesForIpAddresses();
        $ipAddresses = !empty($ipAddresses) ? explode(PHP_EOL, $ipAddresses) : [];
        $check = Utils::checkIpAddressRange($ipAddresses) ? '1' : '0';
        echo json_encode(['check' => $check]);
        wp_die();
    }

    public static function rrzeAccessControlPlugin()
    {
        if (
            !Utils::isPluginActive('rrze-ac/rrze-ac.php')
            && !Utils::isPluginActiveForNetwork('rrze-ac/rrze-ac.php')

        ) {
            return false;
        }

        $currentURL = '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $postId = url_to_postid($currentURL);
        $post = $postId ? get_post($postId) : 0;
        if (
            !($post instanceof \WP_Post)
            || !in_array($post->post_type, ['page', 'attachment'])
        ) {
            return false;
        }

        $meta = get_post_meta($postId, '_access_permission');
        if (empty($meta) || $meta == 'all') {
            return false;
        }

        return true;
    }

    public static function rrzePrivateSitePlugin()
    {
        if (
            !Utils::isPluginActive('rrze-private-site/rrze-private-site.php')
            && !Utils::isPluginActiveForNetwork('rrze-private-site/rrze-private-site.php')

        ) {
            return false;
        }

        if (!defined('PRIVATE_SITE_OPTION') || !get_option(PRIVATE_SITE_OPTION)) {
            return false;
        }

        return true;
    }
}
