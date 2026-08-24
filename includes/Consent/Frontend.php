<?php

namespace RRZE\Legal\Consent;

defined('ABSPATH') || exit;

use RRZE\Legal\Utils;
use function RRZE\Legal\{plugin, consent};

class Frontend {
    private static bool $consentAssetsRegistered = false;

    public static function loaded() {
        add_shortcode('rrzelegal_consent', [__CLASS__, 'handleShortcode']);

        if (self::requestShouldBypassConsent()) {
            return;
        }

        if (did_action('init')) {
            self::init();
        } else {
            add_action('init', [__CLASS__, 'init']);
        }

    }

    /**
     * init function.
     */
    public static function init()
    {
        if (self::requestShouldBypassConsent()) {
            return;
        }

        if (consent()->isBannerActive() || consent()->isTestModeActive()) {
            self::registerConsentAssets();

            // Block scripts
            add_action('template_redirect', [Buffer::getInstance(), 'handleBuffering'], 9998);
            add_filter('script_loader_tag', [ScriptBlocker::getInstance(), 'blockHandles'], 999, 3);
            add_action('wp_footer', [ScriptBlocker::getInstance(), 'handleJavaScriptTagBlocking'], 9998);

            // Add banner
            add_action('wp_footer', [__CLASS__, 'addBanner']);

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

    public static function registerPrivacyEndpointConsentControls(): void {
        self::registerConsentAssets();
    }

    protected static function registerConsentAssets(): void {
        if (self::$consentAssetsRegistered) {
            return;
        }

        self::$consentAssetsRegistered = true;

        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueueScripts']);
        add_action('wp_head', [__CLASS__, 'head']);
        add_action('wp_footer', [__CLASS__, 'footer']);
    }

    
    protected static function requestShouldBypassConsent(): bool {
        return consent()->isCookieForBotsActive()
            && (self::isWhitelistedIp() || consent()->isCurrentUserAgentAllowed());
    }

    /**
     * Check if user IP is whitelisted.
     */
    protected static function isWhitelistedIp(): bool {             
        $ip = self::getClientIp();
        if ($ip === null) {
            return false;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }

        $cidrs = self::getAllWhitelistedCidrs();
        if (empty($cidrs)) {
            return false;
        }

        foreach ($cidrs as $cidr) {
            if (self::ipMatchesCidr($ip, $cidr)) {
                return true;
            }
        }

        return false;
    }
    
    protected static function ipMatchesCidr(string $ip, string $cidr): bool {
        $parts = explode('/', $cidr, 2);
        if (count($parts) !== 2) {
            return false;
        }

        $net = trim($parts[0]);
        $prefixStr = trim($parts[1]);

        if (filter_var($net, FILTER_VALIDATE_IP) === false) {
            return false;
        }
        if (!ctype_digit($prefixStr)) {
            return false;
        }

        $ipBin = inet_pton($ip);
        $netBin = inet_pton($net);
        if ($ipBin === false || $netBin === false) {
            return false;
        }

        if (strlen($ipBin) !== strlen($netBin)) {
            return false; // v4 vs v6 mismatch
        }

        $isV6 = strlen($ipBin) === 16;
        $maxPrefix = $isV6 ? 128 : 32;

        $prefix = (int) $prefixStr;
        if ($prefix < 0 || $prefix > $maxPrefix) {
            return false;
        }

        $bytes = intdiv($prefix, 8);
        $bits = $prefix % 8;

        for ($i = 0; $i < $bytes; $i++) {
            if (ord($ipBin[$i]) !== ord($netBin[$i])) {
                return false;
            }
        }

        if ($bits === 0) {
            return true;
        }

        $mask = (0xFF << (8 - $bits)) & 0xFF;

        return (ord($ipBin[$bytes]) & $mask) === (ord($netBin[$bytes]) & $mask);
    }

    
    /*
     * Get whitelist IP Adress from Multisite Setting rrze_settings 
     * (managed by rrze-settings multisite plugn)
     */
     protected static function getWhitelistedCidrs(): array {
        if (!is_multisite()) {
            return [];
        }

        $settings = get_site_option('rrze_settings');
        if (empty($settings)) {
            return [];
        }

        $value = null;

        if (is_array($settings)) {
            $value = $settings['plugins']['siteimprove_crawler_ip_addresses'] ?? null;
        } elseif (is_object($settings)) {
            $value = $settings->plugins->siteimprove_crawler_ip_addresses ?? null;
        }

        if (!is_array($value)) {
            return [];
        }
        return self::normalizeCidrList($value);
    }

    /*
     * Combine global network whitelist with local settins of plugin
     */
    protected static function getAllWhitelistedCidrs(): array {
        $cidrs = [];

        foreach (self::getWhitelistedCidrs() as $cidr) {
            $cidrs[] = $cidr;
        }

        $option = consent()->getCookiesForIpAddresses();
        if (is_array($option)) {
            foreach (self::normalizeCidrList($option) as $cidr) {
                $cidrs[] = $cidr;
            }
        } elseif (is_string($option)) {
            $list = preg_split('/[\s,;]+/', $option, -1, PREG_SPLIT_NO_EMPTY);
            foreach (self::normalizeCidrList($list) as $cidr) {
                $cidrs[] = $cidr;
            }
        }

        return array_values(array_unique($cidrs));
    }

    
    
    
    protected static function normalizeCidrList(array $cidrs): array {
        $out = [];

        foreach ($cidrs as $cidr) {
            if (!is_string($cidr)) {
                continue;
            }

            $cidr = trim($cidr);
            if ($cidr === '') {
                continue;
            }

            $normalized = self::normalizeSingleCidr($cidr);
            if ($normalized === null) {
                continue;
            }

            $out[] = $normalized;
        }

        return array_values(array_unique($out));
    }

    protected static function normalizeSingleCidr(string $cidr): ?string {
        $ip = $cidr;
        $prefix = null;

        if (strpos($cidr, '/') !== false) {
            $parts = explode('/', $cidr, 2);
            $ip = trim($parts[0]);
            $prefix = trim($parts[1]);
        }

        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return null;
        }

        $isV6 = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
        $maxPrefix = $isV6 ? 128 : 32;

        if ($prefix === null || $prefix === '') {
            $prefix = (string) $maxPrefix;
        }

        if (!ctype_digit($prefix)) {
            return null;
        }

        $p = (int) $prefix;
        if ($p < 0 || $p > $maxPrefix) {
            return null;
        }

        return $ip . '/' . $p;
    }

    
    
    
    protected static function getClientIp(): ?string {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($parts[0]);
        }

        if (!empty($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }

        return null;
    }


    
    public static function enqueueScripts() {
        wp_enqueue_style(
            'rrze-legal-frontend',
            plugins_url('build/rrze-legal.css', plugin()->getBasename()),
            [],
            plugin()->getVersion()
        );
        JavaScript::instance()->registerHead();
    }

    public static function head() {
        JavaScript::instance()->registerHeadFallback();
    }

    public static function footer() {
        JavaScript::instance()->registerFooter();
    }

    public static function addBanner() {
        Banner::add();
    }

    public static function handleShortcode($atts, $content = '')  {
        if (!self::$consentAssetsRegistered) {
            return '';
        }

        return Shortcode::handleShortcode($atts, $content);
    }

    public static function handleCookieBlocking()  {
        CookieBlocker::handleBlocking();
    }

    public static function handleContentBlocking($content) {
        return ContentBlocker::instance()->detectIframes($content);
    }

    public static function handleOembedBlocking($html, $url)  {
        return ContentBlocker::instance()->handleOembed($html, $url);
    }

    public static function handleRRZEVideoBlocking($content)  {
        return ContentBlocker::instance()->handleRRZEVideo($content);
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

        return true;
    }
}
