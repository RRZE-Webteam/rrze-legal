<?php

namespace RRZE\Legal\Network;

defined('ABSPATH') || exit;

use RRZE\Legal\{Settings, Utils};
use function RRZE\Legal\{plugin, fauDomains};

class Options extends Settings
{
    protected bool $hasValidationError = false;

    public function __construct()
    {
        parent::__construct();
        $this->optionName = 'rrze_legal_network';
        $this->settingsFilename = 'network';

        add_filter('rrze_legal_consent_capability', [$this, 'setConsentCapability']);
    }

    /**
     * Execute on 'plugins_loaded' API/action.
     */
    public function loaded()
    {
        if ($this->optionName === '' || $this->settingsFilename === '') {
            return;
        }
        include_once(plugin()->getPath() . "settings/{$this->settingsFilename}.php");
        $this->settings = $settings;
        $this->optionsParent = (object) $this->settings['options_page']['parent'];
        $this->optionsPage = (object) $this->settings['options_page']['page'];
        $this->optionsMenu = (object) $this->settings['options_page']['menu'];
        $this->sections = (object) $this->settings['settings']['sections'];
        $this->setNetworkMenuParent();

        $this->setFields();
        $this->setOptions();
        foreach ($this->fields as $key => $field) {
            $type = $field['type'] ?? '';
            $value = $this->options[$key] ?? '';
            if (is_array($value) && $type == 'textarea') {
                $this->options[$key] = implode(PHP_EOL, $value);
            }
        }
    }

    public function setAdminMenu()
    {
        add_action('network_admin_menu', [$this, 'adminSubMenu']);
        add_action('admin_init', [$this, 'registerSetting']);

        add_action('network_admin_edit_rrze-legal-network-action', [$this, 'save']);
        add_action('network_admin_notices', [$this, 'adminNotices']);
    }

    protected function setNetworkMenuParent()
    {
        if (Utils::isPluginActiveForNetwork('rrze-settings/rrze-settings.php')) {
            $this->optionsParent->slug = 'rrze-settings';
        }
    }

    protected function getNetworkMenuBaseUrl(): string
    {
        if ($this->optionsParent->slug === 'settings.php') {
            return network_admin_url('settings.php');
        }

        return network_admin_url('admin.php');
    }

    public function isOverwriteEndpointsEnabled(): bool
    {
        $options = (array) get_site_option($this->optionName);
        if (!array_key_exists('network_general_overwrite_endpoints', $options)) {
            return true;
        }

        return (bool) $options['network_general_overwrite_endpoints'];
    }

    public function getOrganizationDomainFields(): array {
        $fields = [];
        foreach ($this->getOrganizations() as $key => $label) {
            $fields[] = [
                'name' => $key . '_domains',
                'label' => $label,
                'description' => __('Domainnamen oder eindeutige Domainteile, die dieser Organisation zugeordnet werden. Geben Sie einen Eintrag pro Zeile ein.', 'rrze-legal'),
                'type' => 'textarea',
                'default' => $key === 'fau' ? implode(PHP_EOL, fauDomains()) : '',
                'sanitize_callback' => [$this, 'sanitizeTextareaList'],
            ];
        }
        return $fields;
    }

    protected function getOrganizations(): array {
        $filePath = plugin()->getPath() . 'data/tos.php';
        if (!is_readable($filePath)) {
            return [];
        }

        include $filePath;
        $items = $data['items'] ?? [];
        if (empty($items) || !is_array($items)) {
            return [];
        }

        $organizations = [];
        foreach ($items as $key => $item) {
            $name = $item['name'] ?? $key;
            $organizations[$key] = is_string($name) && $name !== '' ? $name : $key;
        }
        return $organizations;
    }

    /**
     * Set the options.
     * @return array
     */
    protected function setOptions()
    {
        $this->optionName = $this->optionName;
        $defaults = $this->defaultOptions();
        $options = (array) get_site_option($this->optionName);
        $options = wp_parse_args($options, $defaults);
        $this->options = array_intersect_key($options, $defaults);
    }

    /**
     * Displays the corresponding form for each setting sections.
     * @param string $hiddenField Hidden field
     * @return void
     */
    public function settingsForm($hiddenField = '')
    {
        foreach ($this->sections as $section) {
            $sectionId = str_replace('_', '-', $section['id']);
            if ($this->pagePrefix . $sectionId != $this->currentTab) {
                continue;
            }
            $queryArgs = [
                'action' => 'rrze-legal-network-action',
                'current-tab' => $this->pagePrefix . $sectionId
            ];
            if (count($this->allTabs) < 2) {
                unset($queryArgs['current-tab']);
            }
            $action = add_query_arg(
                $queryArgs,
                network_admin_url('edit.php')
            );
            echo '<form id="'. esc_attr($this->pagePrefix . $sectionId) . '" method="post" action="' . esc_attr($action) . '">';
            wp_nonce_field('rrze-legal-network', 'rrze-legal-network-nonce');
            do_settings_sections($this->settingsPrefix . $section['id']);
            settings_fields($this->settingsPrefix . $section['id']);
            submit_button();
            echo '</form>' . PHP_EOL;
        }
    }

    /**
     * Register the settings sections and fields.
     */
    public function registerSetting()
    {
        foreach ($this->sections as $section) {
            if (!isset($section['id']) || !isset($section['title'])) {
                continue;
            }
            $this->addSection($section);
        }
    }

    public function setConsentCapability($capability)
    {
        return is_plugin_active_for_network(plugin()->getBaseName()) && !$this->hasException() ? 'manage_network_options' : $capability;
    }

    public function save()
    {
        check_admin_referer('rrze-legal-network', 'rrze-legal-network-nonce');
        $postOptions = (array) $_POST[$this->optionName] ?? [];
        $options = $this->sanitizeOptions($postOptions);
        if (!$this->hasValidationError) {
            update_site_option($this->optionName, $options);
        }
        $queryArgs['page'] = 'rrze-legal-network-action';
        if (count($this->allTabs) > 1) {
            $queryArgs['current-tab'] = $this->currentTab;
        }
        $queryArgs = [
            'page' => $this->optionsMenu->slug,
            'current-tab' => $this->currentTab,
            'updated' => true
        ];
        if ($this->hasValidationError) {
            unset($queryArgs['updated']);
            $queryArgs['settings-error'] = 'duplicate_organization_domains';
        }
        if (count($this->allTabs) < 2) {
            unset($queryArgs['current-tab']);
        }
        wp_redirect(add_query_arg(
            $queryArgs,
            $this->getNetworkMenuBaseUrl()
        ));
        exit;
    }

    public function adminNotices()
    {
        if (isset($_GET['page']) && $_GET['page'] == $this->optionsMenu->slug && isset($_GET['settings-error']) && $_GET['settings-error'] === 'duplicate_organization_domains') {
            echo '<div id="message" class="notice notice-error is-dismissible"><p>',
            esc_html(__('Die Einstellungen wurden nicht gespeichert. Ein Domainname oder Domainteil darf nur einer Organisation zugeordnet werden.', 'rrze-legal')),
            '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">',
            esc_html(__('Dismiss this notice.', 'rrze-legal')),
            '</span></button></div>';
            return;
        }

        if (isset($_GET['page']) && $_GET['page'] == $this->optionsMenu->slug && isset($_GET['updated'])) {
            echo '<div id="message" class="updated notice is-dismissible"><p>',
            esc_html(__('Settings saved.', 'rrze-legal')),
            '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">',
            esc_html(__('Dismiss this notice.', 'rrze-legal')),
            '</span></button></div>';
        }
    }

    protected function postSanitizeOptions($input, $hasError)
    {
        if (!$hasError && $this->hasDuplicateOrganizationDomains()) {
            $this->hasValidationError = true;
            return $this->options;
        }
        if (!$hasError && $this->options['network_banner_update_version']) {
            $this->updateCookieVersion();
            $this->options['network_banner_update_version'] = '0';
        }
        return $this->options;
    }

    protected function hasDuplicateOrganizationDomains(): bool {
        $domains = [];
        foreach (array_keys($this->getOrganizations()) as $key) {
            $optionKey = 'network_general_' . $key . '_domains';
            $value = (string) ($this->options[$optionKey] ?? '');
            $rows = explode(PHP_EOL, $value);
            foreach ($rows as $row) {
                $domain = $this->normalizeDomainAssignment($row);
                if ($domain === '') {
                    continue;
                }
                if (isset($domains[$domain]) && $domains[$domain] !== $key) {
                    return true;
                }
                $domains[$domain] = $key;
            }
        }
        return false;
    }

    protected function normalizeDomainAssignment(string $domain): string {
        $domain = trim($domain);
        $domain = trim($domain, ". \t\n\r\0\x0B");
        return strtolower($domain);
    }

    /**
     * sanitizeTextareaSitesList
     * @param string $input
     * @return string
     */
    public function sanitizeTextareaSitesList(string $input)
    {
        $list = $this->sanitizeTextareaList($input);
        $sites = explode(PHP_EOL, $list);
        $exceptions = [];
        foreach ($sites as $row) {
            $aryRow = explode(' - ', $row);
            $blogId = isset($aryRow[0]) ? trim($aryRow[0]) : '';
            if (!absint($blogId)) {
                continue;
            }
            switch_to_blog($blogId);
            $url = get_option('siteurl');
            restore_current_blog();
            if (!$url) {
                continue;
            }
            $exceptions[$url] = implode(' - ', [$blogId, $url]);
        }
        ksort($exceptions);
        return !empty($exceptions) ? implode(PHP_EOL, $exceptions) : '';
    }

    /**
     * Site ID has exception.
     * @param string $sectionId
     * @return bool
     */
    public function hasException()
    {
        $exceptions = (string) $this->getOption('network_general', 'exceptions');
        $exceptions = explode(PHP_EOL, $exceptions);
        if (!empty($exceptions) && is_array($exceptions)) {
            foreach ($exceptions as $row) {
                $aryRow = explode(' - ', $row);
                $blogId = isset($aryRow[0]) ? trim($aryRow[0]) : '';
                if (absint($blogId) == get_current_blog_id()) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getCookieVersion()
    {
        return (int) get_site_option('rrze_legal_consent_cookie_version', 1);
    }

    public function sanitizePositiveInteger($input): int {
        $value = absint($input);
        return $value > 0 ? $value : 1;
    }

    public function getTosNoticeWarningDays(): int {
        return $this->getTosNoticeDays('tos_notice_warning_days', 7);
    }

    public function getTosNoticeErrorDays(): int {
        return $this->getTosNoticeDays('tos_notice_error_days', 30);
    }

    public function getTosNoticeWarningText(): string {
        return $this->getTosNoticeText(
            'tos_notice_warning_text',
            __('Eine weitergehende Nichtbearbeitung der Daten führt zu einer Meldung beim CMS Betreiber.', 'rrze-legal')
        );
    }

    public function getTosNoticeErrorText(): string {
        return $this->getTosNoticeText(
            'tos_notice_error_text',
            __('Der CMS Betreiber wurde informiert.', 'rrze-legal')
        );
    }

    public function isTosNoticeAcknowledgementRequired(): bool {
        return (bool) $this->getOption('network_general', 'tos_notice_require_acknowledgement', false);
    }

    protected function getTosNoticeDays(string $name, int $default): int {
        $value = absint($this->getOption('network_general', $name, $default));
        return $value > 0 ? $value : $default;
    }

    protected function getTosNoticeText(string $name, string $default): string {
        $value = trim((string) $this->getOption('network_general', $name, $default));
        return $value !== '' ? $value : $default;
    }

    public function updateCookieVersion()
    {
        $currentVersion = $this->getCookieVersion();
        update_site_option('rrze_legal_consent_cookie_version', $currentVersion + 1);
    }
}
