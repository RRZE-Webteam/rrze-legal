<?php

namespace RRZE\Legal\Consent;

defined('ABSPATH') || exit;

use RRZE\Legal\{Settings, Template, Locale, Utils};
use function RRZE\Legal\{plugin, network, consentCookies};

class Options extends Settings {
    private $isPluginActiveForNetwork;

    public function __construct() {
        parent::__construct();
        $this->optionName = 'rrze_legal_consent';
        $this->settingsFilename = 'consent';

        $this->isPluginActiveForNetwork = Utils::isPluginActiveForNetwork(plugin()->getBaseName());
    }

    protected function postSanitizeOptions($input, $hasError) {
        if (!$hasError && $this->options['banner_update_version']) {
            $this->updateCookieVersion();
            $this->options['banner_update_version'] = '0';
        }
        return $this->options;
    }

    public function getOption(string $section, string $name, $default = '') {
        if ($section === 'banner' && $name === 'domain' && is_multisite()) {
            return $this->getSiteUrlHost();
        }
        if ($section === 'banner' && $name === 'path' && is_multisite()) {
            return '/';
        }
        if ($this->isNetworkManagedOption($section, $name)) {
            return network()->getOption('network_banner', $this->getNetworkManagedOptionName($section, $name), $default);
        }

        return parent::getOption($section, $name, $default);
    }

    public function getSiteUrlHost()
    {
        return Utils::getSiteUrlHost();
    }

    public function getSiteUrlPath()
    {
        return '/';
    }

    public function bannerDefaultDescription()
    {
        $langCode = Locale::getLangCode();
        $tpl = plugin()->getPath(Template::CONSENT_PATH) . 'banner-default-description' . '-' . $langCode . '.html';
        if (!is_readable($tpl)) {
            $tpl = plugin()->getPath(Template::CONSENT_PATH) . 'banner-default-description-en.html';
        }
        return is_readable($tpl) ? $this->getTplContent($tpl) : '';
    }

    protected function getTplContent($template, $options = [])
    {
        $content = Template::getContent($template, $options);
        $content = preg_replace('/(^|[^\n\r])[\r\n](?![\n\r])/', '$1 ', $content);
        return $content;
    }

    public function isServiceProviderActive($provider)
    {
        $serviceProviders = apply_filters('rrze_legal_consent_banner_service_providers', [
            'rrze_rsvp' => 'rrze-rsvp/rrze-rsvp.php',
            'rrze_ratebutton' => 'rrze_ratebutton/rrze_ratebutton.php',
            'siteimprove_analytics' => 'rrze-siteimprove/rrze-siteimprove.php',
            'brmediathek' => 'rrze-video/rrze-video.php',
            'ardmediathek' => 'rrze-video/rrze-video.php',
        ]);
        foreach ($serviceProviders as $key => $value) {
            if ($key === $provider) {
                return (bool) Utils::isPluginActive($value);
            }
        }
    }

    public function hasNetworkPriority() {
        return $this->isPluginActiveForNetwork && (bool) network()->getOption('network_banner', 'status');
    }

    public function getCookieVersion()
    {
        $currentVersion = (int) get_option('rrze_legal_consent_cookie_version', 1);
        if ($this->hasNetworkPriority()) {
            $networkVersion = (int) get_site_option('rrze_legal_consent_cookie_version', 1);
            $currentVersion = $currentVersion + $networkVersion;
        }
        return $currentVersion;
    }

    public function updateCookieVersion()
    {
        $currentVersion = (int) get_option('rrze_legal_consent_cookie_version', 1);
        update_option('rrze_legal_consent_cookie_version', $currentVersion + 1);
    }

    public function isBannerActive()
    {
        if ($this->hasNetworkPriority()) {
            return (bool) network()->getOption('network_banner', 'status');
        } else {
            return (bool) $this->getOption('banner', 'status');
        }
    }

    public function isTestModeActive()
    {
        return ((bool) $this->getOption('banner', 'test_mode') && current_user_can('manage_options'));
    }

    public function hasOnlyEssentialCookies()
    {
        $cookies = 0;
        $options = consentCookies()->getOptions();
        foreach ($options as $value) {
            $category = $value['category'] ?? '';
            if ($category === 'essential') {
                continue;
            }
            if (!empty($value['status'])) {
                $cookies++;
            }
        }
        return empty($cookies);
    }

    public function isCookieForBotsActive()
    {
        if ($this->hasNetworkPriority()) {
            return (bool) network()->getOption('network_banner', 'cookies_for_bots');
        } else {
            return (bool) $this->getOption('banner', 'cookies_for_bots');
        }
    }

    public function isLocalCookieForBotsActive(): bool
    {
        return (bool) parent::getOption('banner', 'cookies_for_bots');
    }

    public function isRespectDoNotTrackActive()
    {
        if ($this->hasNetworkPriority()) {
            return (bool) network()->getOption('network_banner', 'respect_do_not_track');
        } else {
            return (bool) $this->getOption('banner', 'respect_do_not_track');
        }
    }

    public function isReloadAfterOptoutActive()
    {
        if ($this->hasNetworkPriority()) {
            return (bool) network()->getOption('network_banner', 'reload_after_optout');
        } else {
            return (bool) $this->getOption('banner', 'reload_after_optout');
        }
    }

    public function isIgnorePreselectedStatusActive()
    {
        if ($this->hasNetworkPriority()) {
            return (bool) network()->getOption('network_banner', 'ignore_preselected_status');
        } else {
            return (bool) $this->getOption('banner', 'ignore_preselected_status');
        }
    }

    public function getCookiesForIpAddresses()
    {
        $ipAddresses = [];
        if ($this->isPluginActiveForNetwork) {
            $ipAddresses = array_merge(
                $ipAddresses,
                $this->normalizeIpAddressList(network()->getOption('network_banner', 'cookies_for_ip_addresses'))
            );
        }

        $ipAddresses = array_merge(
            $ipAddresses,
            $this->normalizeIpAddressList($this->getOption('banner', 'cookies_for_ip_addresses'))
        );
        $ipAddresses = array_unique(array_filter(array_map('trim', $ipAddresses)));

        return !empty($ipAddresses) ? implode(PHP_EOL, $ipAddresses) : '';
    }

    public function getDefaultUserAgentPatterns(): array
    {
        $filePath = plugin()->getPath() . 'data/useragents.php';
        if (!is_readable($filePath)) {
            return [];
        }

        include $filePath;
        $items = $data['items'] ?? [];
        if (!is_array($items)) {
            return [];
        }

        return array_values(array_filter($items, [$this, 'isValidUserAgentPattern']));
    }

    public function getDefaultUserAgentPatternsDescription(): string
    {
        $items = [];
        foreach ($this->getDefaultUserAgentPatterns() as $pattern) {
            $match = (string) ($pattern['match'] ?? 'contains');
            $value = (string) ($pattern['value'] ?? '');
            if ($value === '') {
                continue;
            }
            if ($match === 'starts_with') {
                $items[] = sprintf(
                    '<li>%s</li>',
                    sprintf(
                        /* translators: %s: User-Agent string. */
                        __('Starts with: %s', 'rrze-legal'),
                        '<code>' . esc_html($value) . '</code>'
                    )
                );
            } else {
                $items[] = sprintf(
                    '<li>%s</li>',
                    sprintf(
                        /* translators: %s: User-Agent string. */
                        __('Contains: %s', 'rrze-legal'),
                        '<code>' . esc_html($value) . '</code>'
                    )
                );
            }
        }

        if (empty($items)) {
            return '';
        }

        return sprintf(
            '<details class="rrze-legal-default-user-agents"><summary>%s</summary><ul>%s</ul></details>',
            esc_html__('Default User-Agent strings:', 'rrze-legal'),
            implode('', $items)
        );
    }

    public function getCookiesForUserAgents(): string
    {
        $userAgents = [];
        if ($this->isPluginActiveForNetwork) {
            $userAgents = array_merge(
                $userAgents,
                $this->normalizeUserAgentList(network()->getOption('network_banner', 'cookies_for_user_agents'))
            );
        }

        $userAgents = array_merge(
            $userAgents,
            $this->normalizeUserAgentList(parent::getOption('banner', 'cookies_for_user_agents'))
        );
        $userAgents = array_unique(array_filter(array_map('trim', $userAgents)));

        return !empty($userAgents) ? implode(PHP_EOL, $userAgents) : '';
    }

    public function isCurrentUserAgentAllowed(): bool
    {
        if (!$this->isCookieForBotsActive()) {
            return false;
        }

        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (!is_string($userAgent) || $userAgent === '') {
            return false;
        }

        foreach ($this->getDefaultUserAgentPatterns() as $pattern) {
            if ($this->matchesUserAgentPattern($userAgent, $pattern)) {
                return true;
            }
        }

        foreach ($this->normalizeUserAgentList($this->getCookiesForUserAgents()) as $needle) {
            if ($needle !== '' && stripos($userAgent, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function normalizeIpAddressList($value): array
    {
        if (empty($value)) {
            return [];
        }
        if (is_array($value)) {
            return $value;
        }
        return explode(PHP_EOL, (string) $value);
    }

    protected function normalizeUserAgentList($value): array
    {
        if (empty($value)) {
            return [];
        }
        if (is_array($value)) {
            return $value;
        }
        return preg_split('/[\r\n]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY);
    }

    protected function isValidUserAgentPattern($pattern): bool
    {
        if (!is_array($pattern)) {
            return false;
        }

        $value = trim((string) ($pattern['value'] ?? ''));
        if ($value === '') {
            return false;
        }

        $match = (string) ($pattern['match'] ?? 'contains');
        return in_array($match, ['contains', 'starts_with'], true);
    }

    protected function matchesUserAgentPattern(string $userAgent, array $pattern): bool
    {
        $value = trim((string) ($pattern['value'] ?? ''));
        if ($value === '') {
            return false;
        }

        $match = (string) ($pattern['match'] ?? 'contains');
        if ($match === 'starts_with') {
            return stripos($userAgent, $value) === 0;
        }

        return stripos($userAgent, $value) !== false;
    }

    protected function isNetworkManagedOption(string $section, string $name): bool
    {
        if (!$this->isPluginActiveForNetwork) {
            return false;
        }

        $managedOptions = [
            'banner' => [
                'secure',
                'lifetime',
                'lifetime_essential_only',
                'headline',
                'description_text',
                'preference_text',
                'accept_all_btn_txt',
                'refuse_btn_txt',
                'save_btn_txt',
            ],
            'content_blocker' => [
                'host_whitelist',
            ],
            'log' => [
                'active',
                'purge_interval',
            ],
        ];

        return in_array($name, $managedOptions[$section] ?? [], true);
    }

    protected function getNetworkManagedOptionName(string $section, string $name): string
    {
        if ($section === 'content_blocker') {
            return 'content_blocker_' . $name;
        }
        if ($section === 'log') {
            return 'log_' . $name;
        }
        return $name;
    }
}
