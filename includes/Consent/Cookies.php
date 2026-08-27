<?php

namespace RRZE\Legal\Consent;

defined('ABSPATH') || exit;

use function RRZE\Legal\{config, consent, consentCookies};

class Cookies {

    /**
     * Check cookie consent
     * @param mixed $cookieId
     * @return boolean
     */
    public static function checkConsent($cookieId)
    {
        $consent = false;

        $cookieName = self::getConsentCookieName();
        if (!empty($_COOKIE[$cookieName])) {
            $cookieData = json_decode(stripslashes($_COOKIE[$cookieName]));

            if (!empty($cookieData->consents)) {
                foreach ($cookieData->consents as $category) {
                    if (in_array($cookieId, $category, true)) {
                        $consent = true;
                        break;
                    }
                }
            }
        }

        return $consent;
    }

    public static function setEssentialCookie()
    {
        $cookieName = self::getConsentCookieName();
        if (!empty($_COOKIE[$cookieName])) {
            return;
        }

        $categories = consentCookies()->getAllCookieCategories();
        if (empty($categories)) {
            return;
        }

        $consents = [];
        foreach ($categories as $category) {
            if (empty($category['cookies'])) {
                continue;
            }

            foreach (array_keys($category['cookies']) as $key) {
                $consents[$category['id']][$category['id']][] = $key;
            }
        }

        $expires = strtotime((string) config()->get('consent_essential_cookie_expiry', '+6 months'));
        $siteUrl = trailingslashit(site_url());
        $parseUrl = wp_parse_url($siteUrl);
        $host = $parseUrl['host'];
        $path = $parseUrl['path'];
        $content = [
            'consents' => $consents['essential'],
            'domainPath' => $host . $path,
            // e.g. Tue, 21 Mar 2023 14:50:55 GMT
            'expires' => gmdate('D, j M Y H:i:s \G\M\T', $expires),
            'uid' => 'anonymous',
            'version' => consent()->getCookieVersion()
        ];
        $content = json_encode($content);

        setcookie(
            $cookieName,
            $content,
            $expires,
            $path,
            $host
        );
    }

    protected static function getConsentCookieName(): string {
        return (string) config()->get('consent_cookie_name', 'rrze-legal-consent');
    }
}
