<?php

namespace RRZE\Legal\Network;

defined('ABSPATH') || exit;

use \RRZE\Legal\Settings;
use function \RRZE\Legal\plugin;

class Options extends Settings
{
    public function __construct()
    {
        parent::__construct();
        $this->optionName = 'rrze_legal_network';
    }

    /**
     * Execute on 'plugins_loaded' API/action.
     */
    public function loaded()
    {
        include_once(plugin()->getPath() . "settings/network.php");
        $this->settings = $settings;
        $this->optionsPage = (object) $this->settings['options_page']['page'];
        $this->optionsMenu = (object) $this->settings['options_page']['menu'];
        $this->sections = (object) $this->settings['settings']['sections'];

        $this->setFields();
        $this->setOptions();

        add_action('network_admin_menu', [$this, 'adminMenu']);
        add_action('admin_init', [$this, 'adminInit']);
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
     * Adds a submenu page to the Settings main menu.
     * @return void
     */
    public function adminMenu()
    {
        foreach ($this->sections as $key => $section) {
            $sectionId = str_replace('_', '-', $section['id']);
            if ($key == 0) {
                $this->defaultTab = $this->pagePrefix . $sectionId;
            }
            $this->allTabs[] = $this->pagePrefix . $sectionId;
        }

        $this->currentTab = array_key_exists('current-tab', $_GET) && in_array($_GET['current-tab'], $this->allTabs) ? $_GET['current-tab'] : $this->defaultTab;

        add_submenu_page(
            'settings.php',
            $this->optionsPage->title,
            $this->optionsMenu->title,
            $this->optionsMenu->capability,
            $this->optionsMenu->slug,
            [$this, 'pageOutput']
        );
    }

    /**
     * Displays the corresponding form for each setting sections.
     * @return void
     */
    public function settingsForm()
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
    protected function registerSetting()
    {
        foreach ($this->sections as $section) {
            if (!isset($section['id']) || !isset($section['title'])) {
                continue;
            }
            $this->addSection($section);
        }
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
}
