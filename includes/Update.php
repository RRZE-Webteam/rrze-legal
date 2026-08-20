<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

class Update
{
    protected const CONSENT_LOG_REMOVAL_VERSION = '2.8.20';

    protected const CONSENT_LOG_REMOVAL_HOOK = 'rrze_legal_remove_consent_log_data';

    protected const CONSENT_LOG_REMOVAL_PROGRESS_OPTION = 'rrze_legal_consent_log_removal_progress';

    protected const CONSENT_LOG_REMOVAL_BATCH_SIZE = 25;

    /**
     * Execute on 'plugins_loaded' API/action.
     * @return void
     */
    public static function loaded()
    {
        add_action(self::CONSENT_LOG_REMOVAL_HOOK, [self::class, 'removeConsentLogDataBatch']);
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

            self::deleteConsentLogNetworkOptions();
            self::scheduleConsentLogDataRemoval();
            return;
        }

        if (get_option($optionName)) {
            return;
        }

        self::deleteConsentLogSiteOptions();
        update_option($optionName, plugin()->getVersion(), false);
    }

    /**
     * Schedule one bounded batch to remove consent log values from network sites.
     */
    protected static function scheduleConsentLogDataRemoval(): void
    {
        if (wp_next_scheduled(self::CONSENT_LOG_REMOVAL_HOOK)) {
            return;
        }

        wp_schedule_single_event(time() + MINUTE_IN_SECONDS, self::CONSENT_LOG_REMOVAL_HOOK);
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
     * Remove consent log data from a bounded number of network sites.
     */
    public static function removeConsentLogDataBatch(): void
    {
        if (!is_multisite()) {
            return;
        }

        $completedOption = 'rrze_legal_consent_log_removed';
        if (get_site_option($completedOption)) {
            return;
        }

        self::deleteConsentLogNetworkOptions();
        $offset = absint(get_site_option(self::CONSENT_LOG_REMOVAL_PROGRESS_OPTION, 0));
        $siteIds = get_sites([
            'fields' => 'ids',
            'number' => self::CONSENT_LOG_REMOVAL_BATCH_SIZE,
            'offset' => $offset,
        ]);

        foreach ($siteIds as $siteId) {
            switch_to_blog((int) $siteId);
            self::deleteConsentLogSiteOptions();
            restore_current_blog();
        }

        if (count($siteIds) < self::CONSENT_LOG_REMOVAL_BATCH_SIZE) {
            delete_site_option(self::CONSENT_LOG_REMOVAL_PROGRESS_OPTION);
            update_site_option($completedOption, plugin()->getVersion());
            return;
        }

        update_site_option(self::CONSENT_LOG_REMOVAL_PROGRESS_OPTION, $offset + count($siteIds));
        self::scheduleConsentLogDataRemoval();
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
