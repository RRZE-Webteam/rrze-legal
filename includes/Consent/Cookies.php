<?php

namespace RRZE\Legal\Consent;

defined('ABSPATH') || exit;

use function RRZE\Legal\{consent, consentCookies};

class Cookies
{
    const CONSENT_COOKIE_NAME = 'rrze-legal-consent';

    /**
     * Check cookie consent
     * @param mixed $cookieId
     * @return boolean
     */
    public static function checkConsent($cookieId)
    {
        $consent = false;

        if (!empty($_COOKIE[self::CONSENT_COOKIE_NAME])) {
            $cookieData = json_decode(stripslashes($_COOKIE[self::CONSENT_COOKIE_NAME]));

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
        if (!empty($_COOKIE[self::CONSENT_COOKIE_NAME])) {
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

        $expires = strtotime('+6 months');
        $siteUrl = trailingslashit(site_url());
        $parseUrl = parse_url($siteUrl);
        $host = $parseUrl['host'];
        $path = $parseUrl['path'];
        $content = [
            'consents' => $consents['essential'],
            'domainPath' => $host . $path,
            // e.g. Tue, 21 Mar 2023 14:50:55 GMT
            'expires' => date('D, j M Y H:i:s \G\M\T', $expires),
            'uid' => 'anonymous',
            'version' => consent()->getCookieVersion()
        ];
        $content = json_encode($content);

        setcookie(
            self::CONSENT_COOKIE_NAME,
            $content,
            $expires,
            $path,
            $host
        );
    }
}
