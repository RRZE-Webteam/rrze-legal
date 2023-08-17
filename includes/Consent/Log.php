<?php

namespace RRZE\Legal\Consent;

defined('ABSPATH') || exit;

use function RRZE\Legal\{consent, consentCategories, consentCookies};

class Log
{
    /**
     * Add consent log entry
     * @param mixed $cookieData
     * @return boolean
     */
    public static function add($cookieData)
    {
        $consents = [];

        // Validate uid
        if (
            !empty($cookieData['uid']) &&
            preg_match('/[0-9a-z]{8}\-[0-9a-z]{8}\-[0-9a-z]{8}\-[0-9a-z]{8}/', $cookieData['uid'])
        ) {
            // Get all valid cookie and categories
            $allowedCategories = consentCategories()->getAllCategoriesNames();
            $allowedCookies = consentCookies()->getAllCookiesNames();

            // Validate consents
            if (!empty($cookieData['consents'])) {
                foreach ($cookieData['consents'] as $category => $cookies) {
                    if (!empty($allowedCategories[$category])) {
                        $consents[$category] = [];

                        if (!empty($cookies)) {
                            foreach ($cookies as $cookie) {
                                if (!empty($allowedCookies[$cookie])) {
                                    $consents[$category][] = $cookie;
                                }
                            }
                        }
                    }
                }
            }

            // Get logs and last log
            $logs = get_option('rrze_legal_consent_log');
            $lastLog = $logs[$cookieData['uid']] ?? [];

            if (!empty($cookieData['version'])) {
                $cookieVersion = (int) $cookieData['version'];
            } else {
                $cookieVersion = consent()->getCookieVersion();
            }

            if (
                empty($lastLog['consents'])
                || ($lastLog['consents'] !== $consents
                    && $lastLog['version'] !== $cookieVersion)
            ) {
                // Update log
                $logs[$cookieData['uid']] = [
                    'uid' => $cookieData['uid'],
                    'version' => $cookieVersion,
                    'consents' => $consents,
                    'timestamp' => time(),
                ];
                update_option('rrze_legal_consent_log', $logs, false);
            }
        }

        return true;
    }

    /**
     * Get consent history.
     * @param mixed $uid
     */
    public static function getConsentHistory($uid)
    {
        $consentHistory = [];

        $uid = trim(strtolower($uid));

        if (!preg_match('/[0-9a-z]{8}\-[0-9a-z]{8}\-[0-9a-z]{8}\-[0-9a-z]{8}/', $uid)) {
            return $consentHistory;
        }

        // Get all available cookie and categories
        $availableCategories = consentCategories()->getAllCategoriesNames();
        $availableCookies = consentCookies()->getAllCookiesNames();

        // Get logs
        $logs = get_option('rrze_legal_consent_log');
        if (!$logs || !is_array($logs)) {
            return $consentHistory;
        }

        foreach ($logs as $logItem) {
            $consentList = [];
            $finalConsentList = [];

            $consents = $logItem['consents'] ?? [];

            if (!empty($consents)) {
                foreach ($consents as $category => $cookies) {
                    if (!empty($availableCategories[$category])) {
                        $consentList[$category]['category'] = $availableCategories[$category];

                        if (!empty($cookies)) {
                            foreach ($cookies as $cookie) {
                                if (!empty($availableCookies[$cookie])) {
                                    $consentList[$category]['cookies'][] = $availableCookies[$cookie];
                                }
                            }
                        }
                    }
                }

                foreach ($consentList as $data) {
                    $finalConsentList[] = $data['category'] . (!empty($data['cookies']) ? ': ' . implode(
                        ', ',
                        $data['cookies']
                    ) : '');
                }
            }

            $consentHistory[] = [
                'version' => $logItem->cookie_version,
                'consent' => implode('<br>', $finalConsentList),
                'timestamp' => $logItem->stamp,
            ];
        }

        return $consentHistory;
    }

    /**
     * Delete consent log
     * @return void
     */
    public static function delete()
    {
        delete_option('rrze_legal_consent_log');
        return true;
    }
}
