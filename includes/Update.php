<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

class Update
{
    protected const CONSENT_LOG_REMOVAL_VERSION = '2.8.20';

    /**
     * Execute on 'plugins_loaded' API/action.
     * @return void
     */
    public static function loaded()
    {
        self::maybeRemoveConsentLogFeatureData();

        $version = get_option(tos()->getOptionName() . '_version', '0');
        if (version_compare($version, '2.0.0', '==')) {
            self::updateToVersion220();
            Utils::redirectToReferer();
        } elseif (version_compare($version, self::CONSENT_LOG_REMOVAL_VERSION, '<')) {
            self::updateToConsentLogRemoval();
        }
    }

    /**
     * Update to version 2.2.0.
     * @return void
     */
    protected static function updateToVersion220()
    {
        $tosOptionName = tos()->getOptionName();
        $cookiesOptionsName = consentCookies()->getOptionName();
        $cookiesOptions = consentCookies()->getOptions();

        foreach ($cookiesOptions as $key => $value) {
            $id = $value['id'] ?? '';
            $category = $value['category'] ?? '';
            if ($category === 'external_media' && $id === 'instagram') {
                unset($cookiesOptions[$key]);
                break;
            }
        }
        update_option($cookiesOptionsName, $cookiesOptions);
        update_option($tosOptionName . '_version', '2.2.0');
    }

    /**
     * Remove the retired consent log feature and its stored data.
     * @return void
     */
    protected static function updateToConsentLogRemoval()
    {
        self::maybeRemoveConsentLogFeatureData();
        update_option(tos()->getOptionName() . '_version', self::CONSENT_LOG_REMOVAL_VERSION);
    }

    /**
     * Remove retired consent log data once, independent from old TOS option versions.
     * @return void
     */
    protected static function maybeRemoveConsentLogFeatureData()
    {
        $optionName = 'rrze_legal_consent_log_removed';
        if (is_multisite()) {
            if (get_site_option($optionName)) {
                return;
            }

            self::deleteConsentLogOptions();
            update_site_option($optionName, plugin()->getVersion());
            return;
        }

        if (get_option($optionName)) {
            return;
        }

        self::deleteConsentLogOptions();
        update_option($optionName, plugin()->getVersion(), false);
    }

    /**
     * Delete consent log data and old consent log setting values.
     * @return void
     */
    protected static function deleteConsentLogOptions()
    {
        if (is_multisite()) {
            self::deleteConsentLogNetworkOptions();
            self::deleteConsentLogOptionsForAllSites();
            return;
        }

        self::deleteConsentLogSiteOptions();
    }

    /**
     * Delete old consent log network settings.
     * @return void
     */
    protected static function deleteConsentLogNetworkOptions()
    {
        $options = get_site_option('rrze_legal_network');
        if (!is_array($options)) {
            return;
        }

        $keys = [
            'network_banner_log_active',
            'network_banner_log_purge_interval',
        ];

        $changed = false;
        foreach ($keys as $key) {
            if (array_key_exists($key, $options)) {
                unset($options[$key]);
                $changed = true;
            }
        }

        if ($changed) {
            update_site_option('rrze_legal_network', $options);
        }
    }

    /**
     * Delete old consent log values for all sites in a multisite network.
     * @return void
     */
    protected static function deleteConsentLogOptionsForAllSites()
    {
        if (!function_exists('get_sites')) {
            self::deleteConsentLogSiteOptions();
            return;
        }

        $siteIds = get_sites([
            'fields' => 'ids',
            'number' => 0,
        ]);

        foreach ($siteIds as $siteId) {
            switch_to_blog((int) $siteId);
            self::deleteConsentLogSiteOptions();
            restore_current_blog();
        }
    }

    /**
     * Delete old consent log values from the current site.
     * @return void
     */
    protected static function deleteConsentLogSiteOptions()
    {
        delete_option('rrze_legal_consent_log');

        $optionNames = [
            'rrze_legal_consent',
            'rrze_legal_consent_de',
            'rrze_legal_consent_en',
        ];

        foreach ($optionNames as $optionName) {
            self::deleteConsentLogKeysFromOption($optionName);
        }
    }

    /**
     * Delete old consent log keys from one option array.
     * @param string $optionName Option name.
     * @return void
     */
    protected static function deleteConsentLogKeysFromOption(string $optionName)
    {
        $options = get_option($optionName);
        if (!is_array($options)) {
            return;
        }

        $keys = [
            'log_active',
            'log_purge_interval',
        ];

        $changed = false;
        foreach ($keys as $key) {
            if (array_key_exists($key, $options)) {
                unset($options[$key]);
                $changed = true;
            }
        }

        if ($changed) {
            update_option($optionName, $options);
        }
    }

}
