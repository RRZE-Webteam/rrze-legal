<?php

namespace RRZE\Legal\Network;

defined('ABSPATH') || exit;

use RRZE\Legal\Settings;
use function RRZE\Legal\plugin;
use function RRZE\Legal\consent;

class Options extends Settings
{
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
            echo '<form id="', $this->pagePrefix . $sectionId . '" method="post" action="' . $action . '">';
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
        update_site_option($this->optionName, $options);
        $queryArgs['page'] = 'rrze-legal-network-action';
        if (count($this->allTabs) > 1) {
            $queryArgs['current-tab'] = $this->currentTab;
        }
        $queryArgs = [
            'page' => $this->optionsMenu->slug,
            'current-tab' => $this->currentTab,
            'updated' => true
        ];
        if (count($this->allTabs) < 2) {
            unset($queryArgs['current-tab']);
        }
        wp_redirect(add_query_arg(
            $queryArgs,
            network_admin_url('settings.php')
        ));
        exit;
    }

    public function adminNotices()
    {
        if (isset($_GET['page']) && $_GET['page'] == $this->optionsMenu->slug && isset($_GET['updated'])) {
            echo '<div id="message" class="updated notice is-dismissible"><p>',
            __('Settings saved.', 'rrze-legal'),
            '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">',
            __('Dismiss this notice.', 'rrze-legal'),
            '</span></button></div>';
        }
    }

    protected function postSanitizeOptions($input, $hasError)
    {
        if (!$hasError && $this->options['network_banner_update_version']) {
            $this->updateCookieVersion();
            $this->options['network_banner_update_version'] = '0';
        }
        return $this->options;
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

    public function updateCookieVersion()
    {
        $currentVersion = $this->getCookieVersion();
        update_site_option('rrze_legal_consent_cookie_version', $currentVersion + 1);
    }
}
