<?php

namespace RRZE\Legal\Consent;

defined('ABSPATH') || exit;

use function RRZE\Legal\{plugin, tos, consent, consentCookies};

class JavaScript
{
    /**
     * Singleton instance.
     * @var mixed
     */
    private static $instance;

    /**
     * Cookie path.
     * @var string
     */
    private $cookiePath;

    /**
     * Cookie version.
     * @var string
     */
    private $cookieVersion;

    /**
     * Content blocker.
     * @var array
     */
    private $contentBlocker = [];

    /**
     * Fallback code.
     * @var array
     */
    private $fallbackCode = [];

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
        $this->cookiePath = consent()->getOption('banner', 'path');
        $this->cookieVersion = consent()->getCookieVersion();
        $this->categories = consentCookies()->getAllCookieCategories();
    }

    /**
     * Register footer script.
     */
    public function registerFooter()
    {
        if (defined('REST_REQUEST')) {
            return;
        }

        wp_enqueue_script(
            'rrze_legal_consent_banner',
            plugins_url('build/banner.js', plugin()->getBasename()),
            ['jquery-core'],
            plugin()->getVersion(),
            true
        );

        $defaultHideBannerOnPages = [
            tos()->endpointUrl('imprint'),
            tos()->endpointUrl('accessibility'),
            tos()->endpointUrl('privacy'),
        ];
        $hideBannerOnUrls = consent()->getOption('banner', 'hide_on_url');
        $hideBannerOnUrls = explode(PHP_EOL, $hideBannerOnUrls);
        $hideBannerOnUrls = array_merge($hideBannerOnUrls, $defaultHideBannerOnPages);

        $jsConfig = [
            'ajaxURL' => admin_url('admin-ajax.php'),
            'animation' => false, //bannerAnimation
            'animationDelay' => false, //bannerAnimationDelay
            'animationIn' => '_rrzelegal-fadeInDown', //bannerAnimationIn
            'animationOut' => '_rrzelegal-flipOutX', //bannerAnimationOut
            'blockContent' => true, //bannerBlocksContent
            'boxLayout' => 'box', //bannerLayout
            'boxLayoutAdvanced' => true,
            'automaticCookieDomainAndPath' => false,
            'cookieDomain' => consent()->getOption('banner', 'domain'),
            'cookiePath' => $this->cookiePath,
            'cookieSecure' => (bool) consent()->getOption('banner', 'secure'),
            'cookieLifetime' => consent()->getOption('banner', 'lifetime'),
            'cookieLifetimeEssentialOnly' => consent()->getOption('banner', 'lifetime_essential_only'),
            'crossDomainCookie' => [],
            'cookieBeforeConsent' => false,
            'cookiesForBots' => consent()->isCookieForBotsActive(),
            'cookieVersion' => $this->cookieVersion,
            'hideBannerOnUrls' => $hideBannerOnUrls,
            'respectDoNotTrack' => consent()->isRespectDoNotTrackActive(),
            'hasOnlyEssentialCookies' => consent()->hasOnlyEssentialCookies(),
            'reloadAfterConsent' => false,
            'reloadAfterOptOut' => consent()->isReloadAfterOptoutActive(),
            'showBanner' => consent()->isBannerActive(),
            'bannerIntegration' => 'javascript',
            'ignorePreSelectStatus' => consent()->isIgnorePreselectedStatusActive(),
            'cookies' => [],
        ];

        $cookies = [];

        if (!empty($this->categories)) {
            foreach ($this->categories as $category) {
                // Add all cookie groups to the array which are needed by the JavaScript class
                $jsConfig['cookies'][$category['id']] = [];

                if (!empty($category['cookies'])) {
                    foreach ($category['cookies'] as $cookieData) {
                        // Add all cookies to the array which are needed by the JavaScript class
                        $jsConfig['cookies'][$category['id']][] = $cookieData['id'];

                        $cookieData = apply_filters(
                            'rrze_legal_cookie_modify_code_' . $cookieData['id'],
                            $cookieData
                        );

                        $cookies[$category['id']][$cookieData['id']] = [
                            'cookieNameList' => CookieBlocker::prepareCookieNamesList($cookieData['cookie_name']),
                            'settings' => [
                                'blockCookiesBeforeConsent' => false,
                                'prioritize' => false,
                                'asyncOptOutCode' => (bool) $cookieData['async_opt_out_code'],
                            ],
                        ];

                        if (
                            !empty($cookieData['opt_in_js']) || !empty($cookieData['opt_out_js'])
                            || !empty($cookieData['fallback_js'])
                        ) {
                            $cookies[$category['id']][$cookieData['id']]['optInJS']
                                = empty($cookieData['prioritize']) ? base64_encode(
                                    do_shortcode($cookieData['opt_in_js'])
                                ) : '';
                            $cookies[$category['id']][$cookieData['id']]['optOutJS'] = base64_encode(
                                do_shortcode($cookieData['opt_out_js'])
                            );
                        }
                    }
                }
            }
        }

        $jsConfig = apply_filters('rrze_legal_consent_banner_js_settings', $jsConfig);

        wp_localize_script('rrze_legal_consent_banner', 'rrzelegalCookieConfig', $jsConfig);
        wp_localize_script('rrze_legal_consent_banner', 'rrzelegalCookieCookies', $cookies);

        $jsCode = 'document.addEventListener("DOMContentLoaded", function (e) {';
        $jsCode .= "\n" . $this->getContentBlockerScriptsData() . "\n";

        $jsCode .= <<<EOT
        var RRZELegalInitCheck = function () {
    
            if (typeof window.RRZELegal === "object" && typeof window.jQuery === "function") {
        
                if (typeof rrzelegalCookiePrioritized !== "object") {
                    rrzelegalCookiePrioritized = { optInJS: {} };
                }
        
                window.RRZELegal.init(rrzelegalCookieConfig, rrzelegalCookieCookies, rrzelegalCookieContentBlocker, rrzelegalCookiePrioritized.optInJS);
            } else {
                window.setTimeout(RRZELegalInitCheck, 50);
            }
        };
        
        RRZELegalInitCheck();
EOT;
        $jsCode .= '});';

        wp_add_inline_script('rrze_legal_consent_banner', $jsCode, 'after');
    }

    /**
     * Register head script.
     */
    public function registerHead()
    {
        $prioritizedCodes = [];

        if (!empty($this->categories)) {
            foreach ($this->categories as $category) {
                if (!empty($category['cookies'])) {
                    foreach ($category['cookies'] as $cookieData) {
                        if (!empty($cookieData['opt_in_js']) || !empty($cookieData['fallback_js'])) {
                            if (!empty($cookieData['prioritize'])) {
                                $prioritizedCodes[$category['id']][$cookieData['id']] = base64_encode(
                                    do_shortcode($cookieData['opt_in_js'])
                                );
                            }

                            $this->fallbackCode[$category['id']][$cookieData['id']] = do_shortcode(
                                $cookieData['fallback_js']
                            );
                        }
                    }
                }
            }
        }

        if (!empty($prioritizedCodes)) {
            if (defined('REST_REQUEST')) {
                return;
            }

            wp_register_script(
                'rrze_legal_consent_banner_prioritize',
                plugins_url('build/prioritize.js', plugin()->getBasename()),
                [],
                plugin()->getVersion()
            );

            wp_localize_script('rrze_legal_consent_banner_prioritize', 'rrzelegalCookiePrioritized', [
                'domain' => consent()->getOption('banner', 'domain'),
                'path' => $this->cookiePath,
                'version' => $this->cookieVersion,
                'bots' => consent()->isCookieForBotsActive(),
                'optInJS' => $prioritizedCodes,
            ]);
        }
    }

    /**
     * Register head fallback script.
     */
    public function registerHeadFallback()
    {
        if (defined('REST_REQUEST')) {
            return;
        }

        // Fallback code is always executed
        if (!empty($this->fallbackCode)) {
            foreach ($this->fallbackCode as $groupData) {
                foreach ($groupData as $cookieFallbackCode) {
                    echo $cookieFallbackCode;
                }
            }
        }
    }

    /**
     * Add content blocker.
     * @param mixed  $contentBlockerId
     * @param string $globalJS
     * @param string $initJS
     * @param mixed  $settings
     */
    public function addContentBlocker($contentBlockerId, $globalJS = '', $initJS = '', $settings = [])
    {
        $settings = apply_filters('rrze_legal_content_blocker_modify_settings_' . $contentBlockerId, $settings);

        $this->contentBlocker[$contentBlockerId] = [
            'contentBlockerId' => $contentBlockerId,
            'global' => $globalJS,
            'init' => $initJS,
            'settings' => $settings,
        ];

        return true;
    }

    /**
     * Get content blocker scripts data.
     */
    public function getContentBlockerScriptsData()
    {
        $js = 'var rrzelegalCookieContentBlocker = {';
        if (!empty($this->contentBlocker)) {
            foreach ($this->contentBlocker as $contentBlockerId => $data) {
                $js .= '"' . $contentBlockerId . '": {';
                $js .= '"id": "' . $contentBlockerId . '",';
                $js .= '"global": function (contentBlockerData) { ' . $data['global'] . ' },';
                $js .= '"init": function (el, contentBlockerData) { ' . $data['init'] . ' },';
                $js .= '"settings": ' . json_encode($data['settings']);
                $js .= '},';
            }

            $js = substr($js, 0, -1);
        }
        $js .= '};';
        return $js;
    }
}
