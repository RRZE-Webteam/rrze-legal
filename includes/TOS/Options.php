<?php

namespace RRZE\Legal\TOS;

defined('ABSPATH') || exit;

use RRZE\Legal\{Settings, Cache, Utils, Locale, Fields};
use RRZE\Legal\TOS\Endpoint;
use function RRZE\Legal\{plugin, network, consent, consentCookies};

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

        $this->isPluginActiveForNetwork = Utils::isPluginActiveForNetwork(plugin()->getBaseName());
    }
    
    
      /**
     * Execute on 'plugins_loaded' API/action.
     */
    public function loaded() {
        if ($this->optionName === '' || $this->settingsFilename === '') {
            return;
        }
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
            $sectionId = $this->getSubmittedSectionId();
            if ($sectionId === 'scope') {
                $organization = sanitize_key((string) ($this->options['scope_context'] ?? ''));
                if (isset($this->staticdata[$organization])) {
                    $previousOrganization = $this->getStoredOrganizationContext();
                    if ($previousOrganization !== '' && $previousOrganization !== $organization) {
                        $this->removeStaticOrganizationOptions($previousOrganization);
                    }
                    $this->options['scope_context'] = $organization;
                }
            } elseif (!$this->hasStoredOrganizationContext()) {
                unset($this->options['scope_context']);
            }
            $this->storeBilingualEditorOptions($input);
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
                'RRZE Legal: %1$s changed the settings for the legal text "%2$s" (%3$s) on site %4$d.',
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
                return __('Imprint', 'rrze-legal');
            case 'privacy':
                return __('Privacy Policy', 'rrze-legal');
            case 'accessibility':
                return __('Accessibility Statement', 'rrze-legal');
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
        wp_localize_script('rrze-legal-settings', 'legalSettings', [
            'dateFormat' => __('yy-mm-dd', 'rrze-legal'),
            'optionName' => $this->optionName,
            'manualPages' => $this->getManualPageSettings(),
        ]);
    }

    public function isCurrentSiteInOrganizationDomains(): bool {
        $hostname = strtolower((string) Utils::getSiteUrlHost());
        if ($hostname === '') {
            return false;
        }

        foreach ($this->getCurrentOrganizationDomains() as $domain) {
            if (str_contains($hostname, $domain)) {
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
            esc_html__('Edit page', 'rrze-legal')
        ) : '';

        $statusMessage = $page->post_status === 'publish'
            ? __('If "Allow manual page" is enabled, this page will be displayed instead of the generated page.', 'rrze-legal')
            : __('This page only overrides the endpoint once it is published and "Allow manual page" is enabled.', 'rrze-legal');

        $message = sprintf(
            /* translators: 1: endpoint slug, 2: edit page link, 3: status message. */
            __('A page with the slug %1$s exists. %3$s %2$s', 'rrze-legal'),
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

    protected function getCurrentOrganizationDomains(): array {
        $organization = $this->getCurrentOrganizationContext();
        if ($organization === '') {
            return [];
        }

        return $this->getOrganizationDomains($organization);
    }

    protected function getCurrentOrganizationContext(): string {
        $organization = $this->getStoredOrganizationContext();
        if ($organization !== '') {
            return $organization;
        }

        $hostname = strtolower((string) Utils::getSiteUrlHost());
        foreach (array_keys($this->staticdata) as $organization) {
            foreach ($this->getOrganizationDomains($organization) as $domain) {
                if (str_contains($hostname, $domain)) {
                    return $organization;
                }
            }
        }

        return 'external';
    }

    protected function getStoredOrganizationContext(): string {
        $optionNames = array_unique([
            $this->getDefaultLanguageOptionName(),
            $this->optionName,
        ]);
        foreach ($optionNames as $optionName) {
            $options = get_option($optionName, []);
            if (!is_array($options)) {
                continue;
            }

            $organization = sanitize_key((string) ($options['scope_context'] ?? ''));
            if (isset($this->staticdata[$organization])) {
                return $organization;
            }
        }

        return '';
    }

    protected function removeStaticOrganizationOptions(string $organization): void {
        $staticOptions = $this->staticdata[$organization] ?? [];
        if (!is_array($staticOptions)) {
            return;
        }

        foreach ($staticOptions as $sectionId => $sectionOptions) {
            if (!is_array($sectionOptions)) {
                continue;
            }

            foreach ($sectionOptions as $name => $value) {
                if ($name === '_readonly') {
                    continue;
                }
                unset($this->options[$sectionId . '_' . $name]);
            }
        }
    }

    protected function hasStoredOrganizationContext(): bool {
        return $this->getStoredOrganizationContext() !== '';
    }

    protected function getOrganizationDomains(string $organization): array {
        $domains = $this->normalizeOrganizationDomains($this->staticdata[$organization]['domains'] ?? []);
        if (!$this->isPluginActiveForNetwork) {
            return $domains;
        }

        $customDomains = network()->getOption('network_general', $organization . '_domains');
        $customDomains = $this->normalizeOrganizationDomains($customDomains);

        return !empty($customDomains) ? $customDomains : $domains;
    }

    protected function normalizeOrganizationDomains($domains): array {
        if (is_string($domains)) {
            $domains = explode(PHP_EOL, $domains);
        }
        if (!is_array($domains)) {
            return [];
        }

        $normalizedDomains = [];
        foreach ($domains as $domain) {
            $domain = strtolower(trim((string) $domain));
            if ($domain !== '') {
                $normalizedDomains[] = $domain;
            }
        }

        return array_values(array_unique($normalizedDomains));
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
                    'description' => sprintf(
                        /* translators: %s: Plugin name. */
                        __('Displayed automatically because the dependent plugin "%s" is active.', 'rrze-legal'),
                        consentCookies()->getPluginDependencyName($value)
                    ),
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
        $this->loadBilingualEditorOptions();
        if (isset($this->fields['privacy_service_providers'])) {
            $this->options['privacy_service_providers'] = $this->getServiceProvidersStatus();
        }
        
 
        $datascope = $this->getCurrentOrganizationContext();
        $this->options['scope_context'] = $datascope;
        $this->staticdataScope = $datascope;


        if ((!empty($datascope)) && (isset($this->staticdata[$datascope]))) {
            $res = [];
            foreach ($this->staticdata[$datascope] as $scopeentry => $data) {
                if ($scopeentry === 'domains') {
                    continue;
                }

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

    /**
     * Loads bilingual editor values from the site's default language options.
     *
     * The fields are edited as German and English pairs in one backend form,
     * therefore they must not depend on the locale of the frontend request.
     * @return void
     */
    protected function loadBilingualEditorOptions(): void {
        $optionName = $this->getDefaultLanguageOptionName();
        if ($optionName === $this->optionName) {
            return;
        }

        $options = get_option($optionName, []);
        if (!is_array($options)) {
            return;
        }

        foreach ($this->getBilingualEditorOptionKeys() as $key) {
            if (array_key_exists($key, $options)) {
                $this->options[$key] = $options[$key];
            }
        }
    }

    /**
     * Stores submitted bilingual editor values in the site's default language options.
     * @param array $input Submitted options
     * @return void
     */
    protected function storeBilingualEditorOptions(array $input): void {
        $optionName = $this->getDefaultLanguageOptionName();
        if ($optionName === $this->optionName) {
            return;
        }

        $options = get_option($optionName, []);
        if (!is_array($options)) {
            $options = [];
        }

        $hasChanges = false;
        foreach ($this->getBilingualEditorOptionKeys() as $key) {
            if (!array_key_exists($key, $input)) {
                continue;
            }

            if (!array_key_exists($key, $options) || $options[$key] !== $input[$key]) {
                $options[$key] = $input[$key];
                $hasChanges = true;
            }
        }

        if ($hasChanges) {
            update_option($optionName, $options);
        }
    }

    /**
     * Returns the options that contain paired German and English editor content.
     * @return array
     */
    protected function getBilingualEditorOptionKeys(): array {
        $keys = [];
        foreach ($this->fields as $field => $options) {
            $type = isset($options['type']) ? strtolower($options['type']) : '';
            if ($type === 'optionalwpeditor') {
                $name = $options['name'] ?? '';
                $sectionId = $name !== '' ? substr($field, 0, -(strlen($name) + 1)) : '';
                if ($sectionId === '') {
                    continue;
                }

                $keys[] = $field;
                foreach (['content_name', 'content_name_en'] as $contentNameProperty) {
                    if (!empty($options[$contentNameProperty])) {
                        $keys[] = $sectionId . '_' . sanitize_key($options[$contentNameProperty]);
                    }
                }
                continue;
            }

            if ($type === 'bilingualwpeditor' && !empty($options['content_name_en'])) {
                $name = $options['name'] ?? '';
                $sectionId = $name !== '' ? substr($field, 0, -(strlen($name) + 1)) : '';
                if ($sectionId === '') {
                    continue;
                }

                $keys[] = $field;
                $keys[] = $sectionId . '_' . sanitize_key($options['content_name_en']);
                continue;
            }

            if ($type !== 'wpeditor' || substr($field, -3) !== '_en') {
                continue;
            }

            $keys[] = substr($field, 0, -3);
            $keys[] = $field;
        }

        return array_values(array_unique($keys));
    }

    /**
     * Returns the option name for the site's default language.
     * @return string
     */
    protected function getDefaultLanguageOptionName(): string {
        return 'rrze_legal_' . substr(Locale::getDefaultLocale(), 0, 2);
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
            $contentName = isset($option['content_name']) ? sanitize_key($option['content_name']) : '';
            $contentDefault = $option['content_default'] ?? '';
            $contentNameEnglish = isset($option['content_name_en']) ? sanitize_key($option['content_name_en']) : '';
            $contentDefaultEnglish = $option['content_default_en'] ?? '';

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
                'rows' => isset($option['rows']) ? absint($option['rows']) : 0,
                'min' => $option['min'] ?? '',
                'max' => $option['max'] ?? '',
                'step' => $option['step'] ?? '',
                'inline' => isset($option['inline']) ? (bool) $option['inline'] : false,
                'disabled' => isset($option['disabled']) ? (bool) $option['disabled'] : false,
                'sanitize_callback' => $option['sanitize_callback'] ?? null,
                'required' => $required,
                'errors' => get_settings_errors($this->settingsPrefix . $section),
                'notice' => $option['notice'] ?? '',
                'content_name' => $contentName,
                'content_name_en' => $contentNameEnglish,
                'content_description' => $option['content_description'] ?? '',
                'content_value' => $contentName !== '' ? $this->getOption($sectionId, $contentName, $contentDefault) : '',
                'content_value_en' => $contentNameEnglish !== '' ? $this->getOption($sectionId, $contentNameEnglish, $contentDefaultEnglish) : '',
                'content_height' => isset($option['content_height']) ? absint($option['content_height']) : 0,
                'content_editor' => !empty($option['content_editor']),
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
                $reason = __('Required field is empty.', 'rrze-legal');
            } elseif (($field['type'] ?? '') === 'email' && !is_email($value)) {
                $reason = __('Required field does not contain a valid email address.', 'rrze-legal');
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
