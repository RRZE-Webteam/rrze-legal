<?php

namespace RRZE\Legal\Consent;

defined('ABSPATH') || exit;

use function RRZE\Legal\{tos, consent, consentCookies};

class ContentBlocker
{
    private static $instance;

    private $currentBlockedContent = '';

    private $currentTitle = '';

    private $currentURL = '';

    private $hosts = [];

    private $hostWhitelist = [];

    private $siteHost = '';

    private $categories = [];

    /**
     * Singleton
     * @return object
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->init();
    }

    /**
     * Initialize
     */
    public function init()
    {
        $this->siteHost = parse_url(get_home_url(), PHP_URL_HOST);

        $hostWhitelist = consent()->getOption('content_blocker', 'host_whitelist');
        $this->hostWhitelist = !empty($hostWhitelist) ? explode(PHP_EOL, $hostWhitelist) : [];

        $this->categories = consentCookies()->getAllCookieCategories();

        foreach ($this->categories as $key => $category) {
            if ($key === 'essential') {
                continue;
            } elseif (empty($category['cookies'])) {
                continue;
            }

            foreach ($category['cookies'] as $cookieData) {
                $contentBlockerId = $cookieData['id'];
                $hosts = !empty($cookieData['hosts']) ? explode(PHP_EOL, $cookieData['hosts']) : [];
                $globalJS = '';
                $initJS = '';
                $settings = ['executeGlobalCodeBeforeUnblocking' => false];
                // Collect data about all active content blockers.
                $this->contentBlocker[$contentBlockerId] = [
                    'content_blocker_id' => $contentBlockerId,
                    'name' => $cookieData['name'],
                    'privacyPolicyURL' => $cookieData['privacy_policy_url'],
                    'hosts' => $hosts,
                    'globalJS' => $globalJS,
                    'initJS' => $initJS,
                    'settings' => $settings,
                ];

                // Build list of available (hosts => content blocker ids).
                if (!empty($hosts)) {
                    foreach ($hosts as $host) {
                        $this->hosts[strtolower($host)] = $contentBlockerId;
                    }
                }

                // Add settings, global js, and init js of the Content Blocker
                JavaScript::instance()->addContentBlocker(
                    $contentBlockerId,
                    $globalJS,
                    $initJS,
                    $settings
                );

                // Register filter of default content blocker.
                add_filter(
                    'rrze_legal_content_blocker_previewHtml_' . $contentBlockerId,
                    [$this, 'previewHtml'],
                    100,
                    2
                );
            }
        }
    }

    /**
     * Detect iFrames.
     * @param mixed $content
     * @return mixed
     */
    public function detectIframes($content)
    {
        if (function_exists('is_feed') && is_feed()) {
            $content = preg_replace('/(\<p\>)?(<iframe.*?(?=<\/iframe>)<\/iframe>){1}(\<\/p\>)?/is', '', $content);
        } else {
            $content = preg_replace_callback(
                '/(\<p\>)?(<iframe.*?(?=<\/iframe>)<\/iframe>){1}(\<\/p\>)?/is',
                [$this, 'handleIframe'],
                $content
            );
        }
        return $content;
    }

    /**
     * Handle Iframe.
     * @param mixed $tags
     * @return mixed
     */
    public function handleIframe($tags)
    {
        $content = $tags[0];
        $srcMatch = [];

        // Test and replace data-src
        if (preg_match('/data-src=("|\')([^"\']{1,})(\1)/i', $tags[2])) {
            if (strpos($tags[2], ' src') === false) {
                $tags[2] = str_replace('data-src', 'src', $tags[2]);
                $tags[0] = str_replace('data-src', 'src', $tags[0]);
            }
        }

        preg_match('/src=("|\')([^"\']{1,})(\1)/i', $tags[2], $srcMatch);

        // Skip iframes without src attribute of where src is about:blank
        if (!empty($srcMatch[2]) && $srcMatch[2] !== 'about:blank') {
            $content = $this->handleContentBlocking($tags[0], $srcMatch[2]);
        }

        return $content;
    }

    /**
     * Handle Oembed.
     *
     * @param mixed $html
     * @param mixed $url
     * @return mixed
     */
    public function handleOembed($html, $url)
    {
        return $this->handleContentBlocking($html, $url);
    }

    /**
     * Handle RRZE-Video Iframes.
     *
     * @param mixed $content
     * @return mixed
     */
    public function handleRRZEVideo($content)
    {
        if (preg_match('/(\<p\>)?(<iframe.*?(?=<\/iframe>)<\/iframe>){1}(\<\/p\>)?/is', $content, $iframeMatch) !== 1) {
            return $content;
        }

        preg_match('/src=("|\')([^"\']{1,})(\1)/i', $iframeMatch[2], $srcMatch);

        // Skip iframes without src attribute of where src is about:blank
        if (!empty($srcMatch[2]) && $srcMatch[2] !== 'about:blank') {
            $content = $this->handleContentBlocking($content, $srcMatch[2]);
        }

        return $content;
    }

    /**
     * Handle content blocking.
     * @param mixed $content
     * @param mixed $url
     * @param mixed $contentBlockerId
     * @param mixed $title
     * @param array $atts
     * @return mixed
     */
    public function handleContentBlocking($content, $url = '', $contentBlockerId = '', $title = '', $atts = [])
    {
        if (empty($url) && !in_array($contentBlockerId, $this->hosts)) {
            return $content;
        }

        if ($this->isHostWhitelisted($url)) {
            return $content;
        }

        if (function_exists('is_feed') && is_feed()) {
            $content = '';
        }

        $this->currentBlockedContent = $content;
        $this->currentURL = !empty($url) ? $url : '';
        $this->currentTitle = $title;

        $currentURLData = parse_url($this->currentURL);

        $detectedContentBlockerId = null;

        // When $contentBlockerId is set - overwrites the by URL detected content blocker
        if (!empty($contentBlockerId) && !empty($this->contentBlocker[$contentBlockerId])) {
            $detectedContentBlockerId = $contentBlockerId;
        } else {
            // Detect content blocker by Host.
            if (!empty($this->hosts) && !empty($this->currentURL) && !empty($currentURLData['host'])) {
                $levenshtein = 0;
                $currentHost = strtolower($currentURLData['host']) . ($currentURLData['path'] ?? '');

                foreach ($this->hosts as $host => $contentBlocker) {
                    if (strpos($currentHost, $host) !== false) {
                        if (
                            (empty($levenshtein) && empty($detectedContentBlockerId))
                            || levenshtein($currentHost, $host) < $levenshtein
                        ) {
                            $levenshtein = levenshtein($currentHost, $host);
                            $detectedContentBlockerId = $contentBlocker;
                        }
                    }
                }
            }
        }

        // Do not block oEmbed of own blog
        if (
            !empty($this->currentURL) && !empty($currentURLData['host'])
            && strpos($currentURLData['host'], $this->siteHost) !== false
        ) {
            $detectedContentBlockerId = null;
        }

        if (!empty($detectedContentBlockerId)) {
            if (has_filter('rrze_legal_content_blocker_previewHtml_' . $detectedContentBlockerId)) {
                $content = apply_filters(
                    'rrze_legal_content_blocker_previewHtml_' . $detectedContentBlockerId,
                    $content,
                    $detectedContentBlockerId
                );
            } else {
                $content = $this->previewHtml(
                    $content,
                    $detectedContentBlockerId,
                    $atts
                );
            }

            $blockedContent = '<script type="text/template">' . base64_encode(
                $this->getCurrentBlockedContent()
            ) . '</script>';

            $content = '<div class="RRZELegal">' . $content
                . '<div class="rrzelegal-hide" data-rrzelegal-cookie-type="content-blocker" data-rrzelegal-cookie-id="'
                . $detectedContentBlockerId . '">' . $blockedContent . '</div></div>';
        }

        // Remove whitespace to avoid WordPress' automatic br- & p-tags & return content.
        return preg_replace('/[\s]+/mu', ' ', $content);
    }

    /**
     * Preview the content.
     * @param mixed $content
     * @param mixed $contentBlockerId
     * @param array $atts
     */
    public function previewHtml($content, $contentBlockerId, $atts = [])
    {
        // Get settings of the Content Blocker
        $contentBlockerData = $this->getContentBlockerData($contentBlockerId);
        if (empty($contentBlockerData)) {
            return $content;
        }

        // Get the title which was maybe set via title-attribute in a shortcode
        $title = $this->getCurrentTitle();

        // If no title was set use the Content Blocker name as title
        if (empty($title)) {
            $title = $contentBlockerData['name'];
        }

        $privacyPolicyUrl = tos()->endpointUrl('privacy') . '#cookies';
        $privacyPolicyLink = sprintf(
            '<a href="%1$s" target="_self" rel="nofollow noopener noreferrer">%2$s</a>',
            $privacyPolicyUrl,
            __('privacy policy', 'rrze-legal')
        );
        $header = __('Display external content', 'rrze-legal');
        $firstText = sprintf(
            /* translators: %s: Name of the service provider. */
            __('At this point content of an external provider (source: %s) is integrated. When displaying, data may be transferred to third parties or cookies may be stored, therefore your consent is required.', 'rrze-legal'),
            $title
        );
        $secondText = sprintf(
            /* translators: %s: Privacy policy link. */
            __('You can find more information and the possibility to revoke your consent in our %s.', 'rrze-legal'),
            $privacyPolicyLink
        );
        $button = __('I agree', 'rrze-legal');

        return sprintf(
            '<div class="_rrzelegal-content-blocker"><div class="_rrzelegal-default"><h3>%1$s</h3><p>%2$s</p><p>%3$s</p><p><a class="_rrzelegal-btn" href="#" data-rrzelegal-cookie-unblock role="button">%4$s</a></p></div></div>',
            $header,
            $firstText,
            $secondText,
            $button
        );
    }

    /**
     * Is Host Whitelisted.
     * @param mixed $host
     * @return boolean
     */
    public function isHostWhitelisted($host)
    {
        $status = false;
        foreach ($this->hostWhitelist as $whitelistHost) {
            if (strpos(strtolower($host), strtolower($whitelistHost)) !== false) {
                $status = true;
            }
        }
        return $status;
    }

    /**
     * Get content blocker data.
     * @param mixed $contentBlockerId
     * @return array
     */
    public function getContentBlockerData($contentBlockerId)
    {
        return $this->contentBlocker[$contentBlockerId] ?? [];
    }

    /**
     * Get current blocked content
     * @return mixed
     */
    public function getCurrentBlockedContent()
    {
        return $this->currentBlockedContent;
    }

    /**
     * Get current title.
     */
    public function getCurrentTitle()
    {
        return $this->currentTitle;
    }
}
