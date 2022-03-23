<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

/**
 * Class Locale
 * This class contains different methods that return, among others,
 * the default language of the website and the language of the current user.
 * Using these methods makes the plugin compatible with the rrze-multilang plugin.
 * @package RRZE\Legal
 */
class Locale
{
    /**
     * Get Available Languages
     * 
     * @return array Return available languages array.
     */
    public static function availableLanguages(): array
    {
        $languages = get_available_languages();
        if (!is_multisite() && defined('WPLANG') && '' !== WPLANG && 'en_US' !== WPLANG && !in_array(WPLANG, $languages, true)) {
            $languages[] = WPLANG;
        }
        foreach ($languages as $language) {
            $langCode = substr($language, 0, 2);
            $languages[$langCode] = $language;
        }
        return $languages;
    }

    /**
     * Get Website Locale
     * 
     * @return string Return webiste locale.
     */
    public static function getLocale(): string
    {
        return get_locale();
    }

    /**
     * Get Language Code
     * 
     * @return string Return the language code.
     */
    public static function getLangCode(): string
    {
        return substr(self::getLocale(), 0, 2);
    }

    /**
     * Get Current User Language
     * 
     * @return string Return the current user language.
     */
    public static function getUserLangCode($userId = 0)
    {
        return substr(self::getUserLocale($userId), 0, 2);
    }

    public static function getUserLocale(int $userId = 0): string
    {
        $defaultLocale = Locale::getDefaultLocale();

        if (!$userId = absint($userId)) {
            if (function_exists('wp_get_current_user')) {
                $currentUser = wp_get_current_user();

                if (!empty($currentUser->locale)) {
                    return $currentUser->locale;
                }
            }

            if (!$userId = get_current_user_id()) {
                return $defaultLocale;
            }
        }

        $locale = get_user_option('locale', $userId);

        if (in_array($locale, self::availableLanguages())) {
            return $locale;
        }

        return $defaultLocale;
    }

    public static function getDefaultLocale()
    {
        static $locale;

        if (defined('WPLANG')) {
            $locale = WPLANG;
        }

        if (is_multisite()) {
            if (wp_installing() || false === $msLocale = get_option('WPLANG')) {
                $msLocale = get_site_option('WPLANG');
            }

            if ($msLocale !== false) {
                $locale = $msLocale;
            }
        } else {
            $dbLocale = get_option('WPLANG');

            if ($dbLocale !== false) {
                $locale = $dbLocale;
            }
        }

        if (!empty($locale)) {
            return $locale;
        }

        return 'en_US';
    }
}
