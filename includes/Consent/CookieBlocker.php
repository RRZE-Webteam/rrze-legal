<?php

namespace RRZE\Legal\Consent;

defined('ABSPATH') || exit;

use RRZE\Legal\Utils;
use function RRZE\Legal\consentCookies;

class CookieBlocker
{
    /**
     * Delete imprecise cookie.
     * @param mixed $cookieName
     * @param mixed $impreciseCookieName
     */
    public static function deleteImpreciseCookie($impreciseCookieName)
    {
        if (!empty($_COOKIE)) {
            $impreciseCookieName = str_replace('*', '', $impreciseCookieName);
            foreach ($_COOKIE as $cookieName => $cookieData) {
                if (strpos($cookieName, $impreciseCookieName) !== false) {
                    self::deleteCookie($cookieName);
                }
            }
        }
    }

    /**
     * Delete precise cookie.
     *
     * @param mixed $cookieName
     */
    public static function deletePreciseCookie($cookieName)
    {
        $domain = Utils::getSiteUrlDomain();
        if (!empty($_COOKIE[$cookieName])) {
            self::deleteCookie($cookieName);
        }
    }

    /**
     * Handle Blocking.
     */
    public static function handleBlocking()
    {
        if (empty($_COOKIE)) {
            return;
        }
        // Get all Cookies were blocking before consent is active
        $categories = consentCookies()->getAllCookieCategories();
        if (empty($categories)) {
            return;
        }
        foreach ($categories as $category) {
            if (empty($category['cookies'])) {
                continue;
            }
            foreach ($category['cookies'] as $cookieData) {
                // Check if consent was given
                if (Cookies::checkConsent($cookieData['id']) === false) {
                    // Find and delete cookies
                    $cookieNameList = self::prepareCookieNamesList($cookieData['cookie_name']);
                    if (empty($cookieNameList)) {
                        continue;
                    }
                    foreach ($cookieNameList as $cookieName) {
                        if (strpos($cookieName, '*') !== false) {
                            self::deleteImpreciseCookie($cookieName);
                        } else {
                            self::deletePreciseCookie($cookieName);
                        }
                    }
                }
            }
        }
    }

    /**
     * Prepare cookie names list.
     * @param mixed $cookieNames
     */
    public static function prepareCookieNamesList($cookieNames)
    {
        $cookieNamesList = [];
        if (!empty($cookieNames) && is_string($cookieNames)) {
            $cookieNames = explode(',', $cookieNames);
            if (!empty($cookieNames)) {
                foreach ($cookieNames as $cookieName) {
                    $cookieName = trim($cookieName);
                    if (!empty($cookieName)) {
                        $cookieNamesList[$cookieName] = $cookieName;
                    }
                }
            }
        }
        return $cookieNamesList;
    }

    protected static function deleteCookie($cookieName)
    {
        $host = Utils::getSiteUrlHost();
        $domain = Utils::getSiteUrlDomain();
        unset($_COOKIE[$cookieName]);
        setcookie($cookieName, '', -1, '/');
        setcookie($cookieName, '', -1, '/', '.' . $host);
        setcookie($cookieName, '', -1, '/', '.' . $domain);
    }
}
