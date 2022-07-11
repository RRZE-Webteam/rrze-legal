<?php

namespace RRZE\Legal\Consent;

defined('ABSPATH') || exit;

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
}
