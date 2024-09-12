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
        Debug::log("loaded Settings, file: ".$this->settingsFilename);
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
            $serviceProviders = $this->options['privacy_service_providers'] ?? [];
            $consentCookiesOptionName = consentCookies()->getOptionName();
            $consentCookiesOptions = consentCookies()->getOptions();
            foreach ($consentCookiesOptions as $key => $value) {
                if ($value['category'] === 'essential') {
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
        wp_register_script(
            'rrze-legal-tos-settings',
            plugins_url('build/tos.js', plugin()->getBasename()),
            ['jquery'],
            plugin()->getVersion()
        );
        wp_localize_script('rrze-legal-tos-settings', 'legalSettings', [
            'optionName' => $this->optionName
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
            $category = $value['category'] ?? '';
            if ($category !== 'essential') {
                $providers[$key] = $value['name'];
            }
        }
        ksort($providers);
        return $providers;
    }

    public function getServiceProvidersStatus(): array  {
        $default = [];
        $options = consentCookies()->getOptions();
        foreach ($options as $key => $value) {
            $category = $value['category'] ?? '';
            if ($category === 'essential') {
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
        $langCode = is_user_logged_in() && is_admin() ? Locale::getUserLangCode() : Locale::getLangCode();
        $this->optionName = $this->optionName . '_' . $langCode;
        $defaults = $this->defaultOptions();
        $options = get_option($this->optionName);
        $options = $options !== false ? $options : [];
        $options = wp_parse_args($options, $defaults);
        $this->options = array_intersect_key($options, $defaults);
        
 // Debug::log("setOptions - option name: ".$this->optionName);
 
        if ((isset($this->options['scope_context'])) && (!empty($this->options['scope_context']))) {
             $datascope = $this->options['scope_context'];
// Debug::log("setOptions - SET SCOPE by options: $datascope ");
        } elseif ((isset($defaults['scope_context'])) && (!empty($defaults['scope_context']))) {
             Debug::log("setOptions - SET SCOPE by defaults: $datascope ");

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
                    echo $subsection['description'];
                };
            } elseif (isset($subsection['callback'])) {
                $callback = $subsection['callback'];
            } else {
                $callback = null;
            }
            
            $readonly = $this->isReadonlySubsection($sectionId . '_' . $subsection['id']);
            
            $startclass = "subsection subsection-".$sectionId . '_' . $subsection['id'];
            if ($readonly) {
                $startclass .= " readonly";
            }
            
            
            $args = array(
                  "before_section" =>  '<div class="'.$startclass.'">',
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
    

 
}
