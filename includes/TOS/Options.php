<?php

namespace RRZE\Legal\TOS;

defined('ABSPATH') || exit;

use RRZE\Legal\{Settings, Cache, Utils, Debug, Locale, Fields};
use RRZE\Legal\TOS\Endpoint;
use function RRZE\Legal\{plugin, network, consent, consentCookies, fauDomains};
// use RRZE\Legal\Debug;

class Options extends Settings {
    private $isPluginActiveForNetwork;
   /**
     * Staticdata
    *  Default Data for TOS-Entries
     * @var object
     */
    protected $staticdata;
    
      /**
     * Scope for StaticData
    *  Default Data for TOS-Entries
     * @var object
     */
    protected $staticdataScope;
   
    /**
     * Overwriting static data 
    *  Default Data for TOS-Entries
     * @var object
     */
    protected $staticdataSet;
    
    public function __construct()  {
        parent::__construct();
        $this->optionName = 'rrze_legal';
        $this->settingsFilename = 'tos';

        add_action('admin_enqueue_scripts', [$this, 'adminEnqueueTOSScripts']);
        add_filter('rrze_legal_privacy_hide_dpo_section', [$this, 'setFAUDpoSection']);

        $this->isPluginActiveForNetwork = Utils::isPluginActiveForNetwork(plugin()->getBaseName());
    }
    
    
      /**
     * Execute on 'plugins_loaded' API/action.
     */
    public function loaded() {
        if ($this->optionName === '' || $this->settingsFilename === '') {
            return;
        }
   //     Debug::log("loaded Settings, file: ".$this->settingsFilename);
        include_once(plugin()->getPath() . "settings/{$this->settingsFilename}.php");
        $this->settings = $settings ?? [];    
        $this->staticdata = $this->loadStaticData();
        $this->updateSettingsContext();
        $this->optionsParent = (object) $this->settings['options_page']['parent'] ?? [];
        $this->optionsPage = (object) $this->settings['options_page']['page'] ?? [];
        $this->optionsMenu = (object) $this->settings['options_page']['menu'] ?? [];
        $this->sections = (object) $this->settings['settings']['sections'] ?? [];

        $this->setFields();
        $this->setOptions();
    }


    protected function postSanitizeOptions($input, $hasError) {
        if (!$hasError) {
            $this->logTosSettingsChange();
            $serviceProviders = $this->options['privacy_service_providers'] ?? [];
            $consentCookiesOptionName = consentCookies()->getOptionName();
            $consentCookiesOptions = consentCookies()->getOptions();
            foreach ($consentCookiesOptions as $key => $value) {
                if (($value['category'] ?? '') === 'essential') {
                    continue;
                }
                if (consentCookies()->hasPluginDependency($value)) {
                    $consentCookiesOptions[$key]['status'] = consentCookies()->isPluginDependencyActive($value) ? '1' : '0';
                    continue;
                }
                if (isset($serviceProviders[$key])) {
                    $consentCookiesOptions[$key]['status'] = '1';
                } else {
                    $consentCookiesOptions[$key]['status'] = '0';
                }
            }
            if (update_option($consentCookiesOptionName, $consentCookiesOptions)) {
                consent()->updateCookieVersion();
                Cache::flush();
            }
        }
        return $this->options;
    }

    protected function logTosSettingsChange(): void {
        $sectionId = $this->getSubmittedSectionId();
        if (!in_array($sectionId, ['imprint', 'privacy', 'accessibility'], true)) {
            return;
        }

        $user = wp_get_current_user();
        $userRole = is_multisite() && $user->exists() && is_super_admin($user->ID)
            ? 'Superadmin'
            : 'Site-Administrator';
        $userLabel = $user->exists()
            ? sprintf('%1$s %2$s (ID %3$d)', $userRole, $user->user_login, $user->ID)
            : __('unknown user', 'rrze-legal');

        do_action(
            'rrze.log.info',
            sprintf(
                'RRZE Legal: %1$s hat die Einstellungen für den Rechtstext "%2$s" (%3$s) auf Website %4$d geändert.',
                $userLabel,
                $this->getTosSectionLogLabel($sectionId),
                $sectionId,
                get_current_blog_id()
            )
        );
    }

    protected function getSubmittedSectionId(): string {
        $optionPage = sanitize_key((string) ($_POST['option_page'] ?? ''));
        $prefix = sanitize_key($this->settingsPrefix);
        if ($optionPage === '' || strpos($optionPage, $prefix) !== 0) {
            return '';
        }
        return substr($optionPage, strlen($prefix));
    }

    protected function getTosSectionLogLabel(string $sectionId): string {
        switch ($sectionId) {
            case 'imprint':
                return __('Impressum', 'rrze-legal');
            case 'privacy':
                return __('Datenschutzerklärung', 'rrze-legal');
            case 'accessibility':
                return __('Barrierefreiheitserklärung', 'rrze-legal');
            default:
                return $sectionId;
        }
    }

    /**
     * Display the admin sub menu page.
     * @return void
     */
    public function subMenuPage()  {
        // flush_rewrite_rules(false);
        wp_enqueue_style('rrze-legal-settings');
        wp_enqueue_script('rrze-legal-settings');
        wp_enqueue_script('rrze-legal-tos-settings');
        echo '<div class="wrap">', PHP_EOL;
        $this->sectionsTabs();
        $this->settingsForm();
        echo '</div>', PHP_EOL;
    }

    /**
     * Register admin scripts.
     * @return void
     */
    public function adminEnqueueTOSScripts()  {
        wp_register_script('rrze-legal-tos-settings', plugins_url('build/tos.js', plugin()->getBasename()), ['jquery'], plugin()->getVersion());
        wp_localize_script('rrze-legal-tos-settings', 'legalSettings', [
            'dateFormat' => __('yy-mm-dd', 'rrze-legal'),
            'optionName' => $this->optionName,
            'manualPages' => $this->getManualPageSettings(),
        ]);
    }

    public function setFAUDpoSection()  {
        if ($this->isCurrentSiteInDefaultDomains()) {
            return true;
        }
        return false;
    }

    public function isCurrentSiteInDefaultDomains() {
        $fauDomains = $this->getFAUDomains();
        $hostname = Utils::getSiteUrlHost();
        foreach ($fauDomains as $domain) {
            if (strpos($hostname, $domain) !== false) {
                return true;
            }
        }
        return false;
    }

    public function overwriteEndpoints()  {
        if ($this->isPluginActiveForNetwork && !network()->hasException()) {
            return (bool) network()->getOption('network_general', 'overwrite_endpoints');
        }
        return true;
    }

    public function canConfigureManualPages(): bool {
        if (!is_multisite()) {
            return true;
        }

        return $this->overwriteEndpoints();
    }

    public function isManualPageAllowed(string $endpoint): bool {
        if (!$this->canConfigureManualPages()) {
            return false;
        }

        return (bool) $this->getOption($endpoint, 'allow_manual_page');
    }

    public function hasPublishedManualPage(string $endpoint): bool {
        $page = $this->getManualPage($endpoint);
        if (!($page instanceof \WP_Post)) {
            return false;
        }

        return $page->post_status === 'publish';
    }

    public function manualPageNotice(string $endpoint): string {
        $page = $this->getManualPage($endpoint);
        if (!($page instanceof \WP_Post)) {
            return '';
        }

        $slugs = Endpoint::getSlugs();
        $slug = $slugs[$endpoint] ?? '';
        $editUrl = get_edit_post_link($page->ID, '');
        $editLink = $editUrl ? sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url($editUrl),
            esc_html__('Seite bearbeiten', 'rrze-legal')
        ) : '';

        $statusMessage = $page->post_status === 'publish'
            ? __('Wenn "Manuelle Seite erlauben" aktiviert ist, wird diese Seite statt der generierten Seite angezeigt.', 'rrze-legal')
            : __('Diese Seite überschreibt den Endpoint erst, wenn sie veröffentlicht ist und "Manuelle Seite erlauben" aktiviert ist.', 'rrze-legal');

        $message = sprintf(
            /* translators: 1: endpoint slug, 2: edit page link, 3: status message. */
            __('Es existiert eine Seite mit dem Slug %1$s. %3$s %2$s', 'rrze-legal'),
            '<code>' . esc_html($slug) . '</code>',
            $editLink,
            esc_html($statusMessage)
        );

        return '<div class="notice notice-info inline rrze-legal-manual-page-notice"><p>' . $message . '</p></div>';
    }

    protected function getManualPage(string $endpoint): ?\WP_Post {
        $slugs = Endpoint::getSlugs();
        if (!isset($slugs[$endpoint])) {
            return null;
        }

        $page = get_page_by_path($slugs[$endpoint], OBJECT, 'page');
        if (!($page instanceof \WP_Post)) {
            return null;
        }

        return $page;
    }

    public function isManualPageOverriding(string $endpoint): bool {
        return $this->isManualPageAllowed($endpoint) && $this->hasPublishedManualPage($endpoint);
    }

    protected function getManualPageSettings(): array {
        $settings = [];
        foreach (array_keys(Endpoint::defaultSlugs()) as $endpoint) {
            $settings[$endpoint] = [
                'exists' => $this->hasPublishedManualPage($endpoint),
                'overrides' => $this->isManualPageOverriding($endpoint),
            ];
        }
        return $settings;
    }

    public function getFAUDomains() {
        $fauDomains = fauDomains();
        if ($this->isPluginActiveForNetwork) {
            $customDomains = network()->getOption('network_general', 'fau_domains');
            if (!empty($customDomains)) {
                $fauDomains = explode(PHP_EOL, $customDomains);
            }
        }
        return $fauDomains;
    }

    public function getFAUDomainsToString()  {
        return implode(PHP_EOL, $this->getFAUDomains());
    }

    public function getSiteUrlHost()  {
        return Utils::getSiteUrlHost();
    }

    public function endpoints(): array  {
        return Endpoint::defaultSlugs();
    }

    public function endpointTitle(string $slug): string {
        return Endpoint::endpointTitle($slug);
    }

    public function endpointUrl(string $slug): string {
        return Endpoint::endpointUrl($slug);
    }

    public function endpointLink(string $slug): string {
        return Endpoint::endpointLink($slug);
    }

    public function isNewsletterActive() {
        return Utils::isPluginActive('rrze-newsletter/rrze-newsletter.php');
    }

    public function isRsvpActive() {
        return Utils::isPluginActive('rrze-rsvp/rrze-rsvp.php');
    }

    public function getServiceProvidersOptions(): array  {
        $providers = [];
        $options = consentCookies()->getOptions();
        foreach ($options as $key => $value) {
            if (($value['category'] ?? '') === 'essential') {
                continue;
            }
            if (consentCookies()->hasPluginDependency($value)) {
                if (!consentCookies()->isPluginDependencyActive($value)) {
                    continue;
                }
                $providers[$key] = [
                    'label' => $value['name'],
                    'disabled' => true,
                    'description' => __('Wird automatisch angezeigt, weil das abhängige Plugin aktiv ist.', 'rrze-legal'),
                ];
                continue;
            }
            $providers[$key] = $value['name'];
        }
        ksort($providers);
        return $providers;
    }

    public function getServiceProvidersStatus(): array  {
        $default = [];
        $options = consentCookies()->getOptions();
        foreach ($options as $key => $value) {
            if (($value['category'] ?? '') === 'essential') {
                continue;
            }
            $status = !empty($value['status']) ? '1' : '0';
            $default[$key] = $status;
        }
        ksort($default);
        return $default;
    }

    
    /**
     * Set the option values.
     * @return array
     */
    protected function setOptions() {
        $langCode = Locale::getLangCode();
        $this->optionName = $this->optionName . '_' . $langCode;
        $defaults = $this->defaultOptions();
        $options = get_option($this->optionName);
        $options = $options !== false ? $options : [];
        $options = wp_parse_args($options, $defaults);
        $this->options = array_intersect_key($options, $defaults);
        if (isset($this->fields['privacy_service_providers'])) {
            $this->options['privacy_service_providers'] = $this->getServiceProvidersStatus();
        }
        
 
        if ((isset($this->options['scope_context'])) && (!empty($this->options['scope_context']))) {
             $datascope = $this->options['scope_context'];
        } elseif ((isset($defaults['scope_context'])) && (!empty($defaults['scope_context']))) {

            $datascope  = $defaults['scope_context'];
            $this->options['scope_context'] = $datascope;
        }
        $this->staticdataScope = $datascope;


        if ((!empty($datascope)) && (isset($this->staticdata[$datascope]))) {
            $res = [];
            foreach ($this->staticdata[$datascope] as $scopeentry => $data) {
                $setreadonly = false;
                if (is_array($data)) {
                    if ((isset($data['_readonly'])) && ($data['_readonly'] === true)) {
                        $setreadonly = true;
                    }
                    foreach ($data as $name => $value) {
                        if ($name !== '_readonly') {
                            $res[$scopeentry.'_'.$name] = $value;
                            $this->options[$scopeentry.'_'.$name] = $value;
                            // overwrite data, cause its all readonly
                            $res[$scopeentry.'_'.$name.'_readonly'] = $setreadonly;
                        }
                    }

                }
            }
            $this->staticdataSet = $res;

        }
                
    }
    
    
    /*
     * check if a subsection is marked as readonly in the current context
     * @param string $sectionId
     * @return bool
     */
     protected function isReadonlySubsection(string $sectionId) {
         if ((isset($this->staticdataScope)) && (isset($this->staticdata))) {
             $datascope = $this->staticdataScope;
             
             if ((!empty($datascope)) && (isset($this->staticdata[$datascope]))) { 
                if (isset($this->staticdata[$datascope][$sectionId])) {
                    if ((isset($this->staticdata[$datascope][$sectionId]['_readonly'])) &&  ($this->staticdata[$datascope][$sectionId]['_readonly']===true)) {      
                        return true;
                    }
                }
            }
         }
         return false; 
     }
    
     /**
     * Add a subsection to the settings page.
     * @param string $sectionId
     * @param array $subsections
     * @param string $capability
     * @return void
     */
    protected function addSubsections(string $sectionId, array $subsections, string $capability)  {
        $defaultCap = $capability;
        foreach ($subsections as $subsection) {
            if (!isset($subsection['id']) || !isset($subsection['title'])) {
                continue;
            }
            $capability = isset($subsection['capability']) ? $subsection['capability'] : $defaultCap;
            if (!current_user_can($capability)) {
                continue;
            }
            if (isset($subsection['hide_section']) && (bool) $subsection['hide_section']) {
                continue;
            }
            if (!isset($subsection['fields'])) {
                continue;
            }
            if (!empty($subsection['description'])) {
                $subsection['description'] = '<div class="inside">' . $subsection['description'] . '</div>';
                $callback = function () use ($subsection) {
                    echo wp_kses($subsection['description'], 'post');
                };
            } elseif (isset($subsection['callback'])) {
                $callback = $subsection['callback'];
            } else {
                $callback = null;
            }
            
            $readonly = $this->isReadonlySubsection($sectionId . '_' . $subsection['id']);
            $manualPageOverridden = $subsection['id'] !== 'manual_page' && $this->isManualPageOverriding($sectionId);
            
            $startclass = "subsection subsection-".$sectionId . '_' . $subsection['id'];
            if ($readonly) {
                $startclass .= " readonly";
            }
            if ($manualPageOverridden) {
                $startclass .= " rrze-legal-manual-page-disabled";
            }
            
            
            $args = array(
                  "before_section" =>  '<div class="'.esc_attr($startclass).'">',
                  "after_section"   => '</div>',
                  "section_class" => "subsection-".$this->settingsPrefix . $sectionId . '_' . $subsection['id']
            );
           //     Debug::log("addSubsections - add_settings_section: ".$this->settingsPrefix . $sectionId . '_' . $subsection['id']);
            add_settings_section(
                $this->settingsPrefix . $sectionId . '_' . $subsection['id'],
                !isset($subsection['hide_title']) || (bool) !$subsection['hide_title'] ? $subsection['title'] : '',
                $callback,
                $this->settingsPrefix . $sectionId,
                $args
            );
            $this->addFields($sectionId, $subsection);
        }
    }
    
 /**
     * Add fields to the settings page.
     * @param string $sectionId
     * @param array $subsection
     * @return void
     */
    protected function addFields(string $sectionId, array $subsection = []) {
        $fields = $subsection['fields'] ?? $this->fields;
        $subsectionId = $subsection['id'] ?? '';
        foreach ($fields as $key => $option) {
            if (!$subsectionId && strpos($key, $sectionId . '_') !== 0) {
                continue;
            }
            $name = $option['name'] ?? '';
            if (!isset($this->fields[$sectionId . '_' . $option['name']])) {
                continue;
            }
            
            $option = $this->updateOptionByStaticdata($sectionId . '_' . $option['name'], $option); 
            
            
            $type = isset($option['type']) ? strtolower($option['type']) : '';
            $callback = Fields::callback($type);
            if (!is_callable($callback)) {
                continue;
            }

            $label = $option['label'] ?? '';
            $section = $sectionId . ($subsectionId ? '_' . $subsectionId : '');
            $default = $option['default'] ?? '';
            $value = $this->getOption($sectionId, $name, $default);
            $required = isset($option['required']) ? (bool) $option['required'] : false;

            $atts = [
                'name' => $name,
                'id' => $this->settingsPrefix . $sectionId . '_' . $name,
                'label' => $label,
                'type' => $type,
                'description' => $option['description'] ?? '',
                'options' => $option['options'] ?? '',
                'default' => $default,
                'placeholder' => $option['placeholder'] ?? '',
                'section' => $sectionId,
                'option_name' => $this->optionName,
                'value' => $value,
                'size' => $option['size'] ?? '',
                'height' => isset($option['height']) ? absint($option['height']) : 0,
                'min' => $option['min'] ?? '',
                'max' => $option['max'] ?? '',
                'step' => $option['step'] ?? '',
                'inline' => isset($option['inline']) ? (bool) $option['inline'] : false,
                'disabled' => isset($option['disabled']) ? (bool) $option['disabled'] : false,
                'sanitize_callback' => $option['sanitize_callback'] ?? null,
                'required' => $required,
                'errors' => get_settings_errors($this->settingsPrefix . $section),
                'notice' => $option['notice'] ?? '',
            ];

            $atts = Fields::matchAtts($atts);

            add_settings_field(
                "{$section}[{$name}]",
                $required ? $label . ' *' : $label,
                $callback,
                $this->settingsPrefix . $sectionId,
                $this->settingsPrefix . $section,
                $atts
            );
        }
    }


    
    /*
     *  update scope context by staticdata
     */

    protected function updateSettingsContext() {
        // Fill options of scope selector with avaible scops and their names, cause they are not 
        // set via settings
        if (isset($this->staticdata)) {
            $setoptions = [];
            foreach ($this->staticdata as $scope => $data) {
                if ((isset($data['name'])) && (!empty($data['name']))) {
                    $setoptions[$scope] = $data['name'];
                }
            }
            if (!empty($setoptions)) {
                foreach ($this->settings['settings']['sections'] as $num => $sections) {
                    if ($this->settings['settings']['sections'][$num]['id'] == 'scope') {
                        foreach ($this->settings['settings']['sections'][$num]['subsections'] as $sub => $opts) {
                            if ($this->settings['settings']['sections'][$num]['subsections'][$sub]['id'] == 'scope') {
                                foreach ($this->settings['settings']['sections'][$num]['subsections'][$sub]['fields'] as $fields => $entry ) {
                                        if ($this->settings['settings']['sections'][$num]['subsections'][$sub]['fields'][$fields]['name'] == 'context') {
                                            $this->settings['settings']['sections'][$num]['subsections'][$sub]['fields'][$fields]['options'] = $setoptions;
                                        }
                                }
                            }
                        }
                    } 
                }
            }
        }
    }
    
      
    /*
    * checks the current field setting option for static data, 
    * that has to overwrite the previous data or defaults.
    * If no change is need it will return the data untouched. 
    */
    protected function updateOptionByStaticdata(string $id, array $current): array {
        if (isset($this->staticdataSet))  {
           if (isset($this->staticdataSet[$id])) {
               $current['default'] = $this->staticdataSet[$id];
               if ((isset($this->staticdataSet[$id."_readonly"])) && ($this->staticdataSet[$id."_readonly"] === true)){
                   $current['value'] = $this->staticdataSet[$id];
                   $current['disabled'] = true;
               }
           }
        }
        return $current;
    }
    
     /**
     * Check for existing data file in data/ , that contains default values and data input
     * @param type $filename
     *    filename data/$filename.php
     *
     * @return array
     */
    public function loadStaticData(): array {
        $file_path = plugin()->getPath() . "data/{$this->settingsFilename}.php";
        if (file_exists($file_path)) {
            // Lade die Datei, falls sie existiert
            include $file_path;

            $staticData = $data['items'] ?? [];
            
            if ( !empty($staticData) &&  is_array($staticData) ) {
                return $staticData;                
            }
            
        }
        return []; 
    }
    
    public function getRequiredDataIssues(): array {
        $issues = [];
        $options = $this->options;

        foreach ($this->fields as $key => $field) {
            $sectionId = explode('_', $key)[0];
            if ($this->isManualPageOverriding($sectionId)) {
                continue;
            }

            $required = isset($field['required']) ? (bool) $field['required'] : false;
            if (!$required) {
                continue;
            }

            $value = isset($options[$key]) ? trim((string) $options[$key]) : '';
            $reason = '';
            if ($value === '') {
                $reason = __('Pflichtfeld ist leer.', 'rrze-legal');
            } elseif (($field['type'] ?? '') === 'email' && !is_email($value)) {
                $reason = __('Pflichtfeld enthält keine gültige E-Mail-Adresse.', 'rrze-legal');
            } elseif ($this->isPlaceholderDefaultValue($key, $field, $value)) {
                $reason = __('Pflichtfeld enthält noch einen automatisch gesetzten Standardwert.', 'rrze-legal');
            }

            if ($reason === '') {
                continue;
            }

            $issues[] = [
                'section' => $sectionId,
                'section_label' => $this->getSectionLabel($sectionId),
                'field' => $key,
                'field_label' => wp_strip_all_tags((string) ($field['label'] ?? $key)),
                'reason' => $reason,
                'edit_url' => $this->getRequiredDataIssueEditUrl($sectionId),
            ];
        }

        return $issues;
    }

    public function syncRequiredDataNoticeTimestamp(array $issues): int {
        $optionName = $this->getRequiredDataNoticeTimestampOptionName();
        if (empty($issues)) {
            delete_option($optionName);
            delete_option($this->getRequiredDataNoticeLogOptionName('warning'));
            delete_option($this->getRequiredDataNoticeLogOptionName('error'));
            return 0;
        }

        $timestamp = (int) get_option($optionName, 0);
        if ($timestamp <= 0) {
            $timestamp = current_time('timestamp');
            update_option($optionName, $timestamp, false);
        }

        return $timestamp;
    }

    public function hasRequiredDataNoticeTimestamp(): bool {
        return $this->getRequiredDataNoticeTimestamp() > 0;
    }

    public function getRequiredDataNoticeTimestamp(): int {
        return (int) get_option($this->getRequiredDataNoticeTimestampOptionName(), 0);
    }

    public function hasRequiredDataNoticeLog(string $level): bool {
        return (bool) get_option($this->getRequiredDataNoticeLogOptionName($level), false);
    }

    public function markRequiredDataNoticeLog(string $level): void {
        update_option($this->getRequiredDataNoticeLogOptionName($level), current_time('timestamp'), false);
    }

    public function formatRequiredDataNoticeTimestamp(int $timestamp): string {
        if ($timestamp <= 0) {
            return '';
        }

        return date_i18n(
            get_option('date_format') . ' ' . get_option('time_format'),
            $timestamp
        );
    }

    protected function getRequiredDataNoticeTimestampOptionName(): string {
        return $this->optionName . '_required_data_first_reported';
    }

    protected function getRequiredDataNoticeLogOptionName(string $level): string {
        return $this->optionName . '_required_data_' . sanitize_key($level) . '_logged';
    }

    protected function isPlaceholderDefaultValue(string $key, array $field, string $value): bool {
        $default = isset($field['default']) ? trim((string) $field['default']) : '';
        if ($default === '' || $value !== $default) {
            return false;
        }

        $placeholderDefaults = [
            'imprint_responsible_person_organization' => $this->getSiteUrlHost(),
            'imprint_webmaster_email' => get_option('admin_email'),
            'accessibility_feedback_email' => get_option('admin_email'),
        ];

        return isset($placeholderDefaults[$key]) && $value === trim((string) $placeholderDefaults[$key]);
    }

    protected function getSectionLabel(string $sectionId): string {
        foreach ((array) $this->sections as $section) {
            if (($section['id'] ?? '') === $sectionId) {
                return wp_strip_all_tags((string) ($section['title'] ?? $sectionId));
            }
        }

        return $sectionId;
    }

    protected function getRequiredDataIssueEditUrl(string $sectionId): string {
        return add_query_arg(
            [
                'page' => 'legal',
                'current-tab' => $this->pagePrefix . str_replace('_', '-', $sectionId),
            ],
            admin_url('admin.php')
        );
    }
    
    
 
}
