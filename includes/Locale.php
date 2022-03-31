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
     * Get all available languages based on the presence of *.mo files in WP_LANG_DIR.
     * @return array Available languages array.
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
     * Get the current website locale.
     * @return string The locale of the website or from the 'locale' hook.
     */
    public static function getLocale(): string
    {
        return get_locale();
    }

    /**
     * Get the first two characters of the locale.
     * @return string The ISO 639-1 language code.
     */
    public static function getLangCode(): string
    {
        return substr(self::getLocale(), 0, 2);
    }

    /**
     * Get the first two characters of the user locale.
     * @param int $userId The user ID.
     * @return string The ISO 639-1 language code.
     */
    public static function getUserLangCode(int $userId = 0): string
    {
        return substr(self::getUserLocale($userId), 0, 2);
    }

    /**
     * Get the current user locale.
     * @param int $userId The user ID.
     * @return string The locale of the user.
     */
    public static function getUserLocale(int $userId = 0): string
    {
        $defaultLocale = self::getDefaultLocale();

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

    /**
     * Get the default website locale.
     * @return string The default locale of the website.
     */
    public static function getDefaultLocale(): string
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
