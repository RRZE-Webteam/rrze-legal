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
        
 Debug::log("setOptions - option name: ".$this->optionName);
 
        if ((isset($this->options['scope_context'])) && (!empty($this->options['scope_context']))) {
             $datascope = $this->options['scope_context'];
Debug::log("setOptions - SET SCOPE by options: $datascope ");
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
            // Lade die Datei ein, falls sie existiert
            include $file_path;

            $staticData = $data['items'] ?? [];
            
            if ( !empty($staticData) &&  is_array($staticData) ) {
                return $staticData;                
            }
            
        }
        return []; 
    }
    

    
    public function getLegalAreaOptions() {
        $options = [];
        foreach ($this->getLegalAreaData() as $key => $area) {
            $options[$key] = $area['region'];
        }
        return $options;
    }

    public function getLegalAreaData() {
        return (object) $this->legalAreaData();
    }

    protected function legalAreaData()  {
        return [
            [
                'region' => __('Bundesebene Deutschland (Öffentlicher Dienst)', 'rrze-legal'),
                'url_law' => 'https://www.gesetze-im-internet.de/bgg/BGG.pdf',
                'url_vo' => 'https://www.gesetze-im-internet.de/bitv_2_0/BJNR184300011.html',
                'controlling' => 'Überwachungsstelle des Bundes für Barrierefreiheit von Informationstechnik',
                'controlling_namezusatz' => 'Bundesministerium für Arbeit und Soziales',
                'controlling_email' => '',
                'controlling_url' => 'https://www.bfit-bund.de',
                'controlling_plz' => '10117',
                'controlling_city' => 'Berlin',
                'controlling_street' => 'Wilhelmstraße 49',
            ],
            [
                'region' => __('Baden-Württemberg', 'rrze-legal'),
                'url_law' => 'http://www.landesrecht-bw.de/jportal/?quelle=jlink&query=BehGleichStG+BW&psml=bsbawueprod.psml&max=true',
                'url_vo' => 'http://www.landesrecht-bw.de/jportal/?quelle=jlink&query=BehGleichStGDV+BW&psml=bsbawueprod.psml&max=true&aiz=true',
                'controlling' => 'Überwachungsstelle für mediale Barrierefreiheit des Landes Baden-Württemberg',
                'controlling_email' => 'ueberwachungsstelle@drv-bw.de',
                'controlling_url' => 'https://www.deutsche-rentenversicherung.de/BadenWuerttemberg/DE/Ueber-uns/Mediale-Barrierefreiheit/mediale-barrierefreiheit.html',
                'controlling_address' => '',
            ],
            [
                'region' => __('Bayern', 'rrze-legal'),
                'url_law' => 'http://gesetze-bayern.de/Content/Document/BayBGG/true',
                'url_vo' => 'https://www.gesetze-bayern.de/Content/Document/BayBITV',
                'controlling' => 'Landesamt für Digitalisierung, Breitband und Vermessung',
                'controlling_namezusatz' => 'IT-Dienstleistungszentrum des Freistaats Bayern Durchsetzungs- und Überwachungsstelle für barrierefreie Informationstechnik',
                'controlling_email' => 'bitv@bayern.de',
                'controlling_url' => 'https://www.ldbv.bayern.de/digitalisierung/bitv.html',
                'controlling_phone' => '+49 89 2129-1111',
                'controlling_plz' => '81541',
                'controlling_city' => 'München',
                'controlling_street' => 'St.-Martin-Straße 47',

            ],
            [
                'region' => __('Berlin', 'rrze-legal'),
                'url_law' => 'http://gesetze.berlin.de/jportal/?quelle=jlink&query=BIKTG+BE+%C2%A7+3&psml=bsbeprod.psml&max=true',
                'url_vo' => '',
                'controlling' => 'Landesbeauftragte für digitale Barrierefreiheit',
                'controlling_email' => 'Digitale-Barrierefreiheit@senInnDS.berlin.de',
                'controlling_url' => 'https://www.berlin.de/moderne-verwaltung/barrierefreie-it/',
                'controlling_plz' => '10179',
                'controlling_city' => 'Berlin',
                'controlling_street' => 'Klosterstraße 47',
            ],
            [
                'region' => __('Brandenburg', 'rrze-legal'),
                'url_law' => 'https://bravors.brandenburg.de/gesetze/bbgbgg',
                'url_vo' => 'https://bravors.brandenburg.de/verordnungen/bbgbitv',
                'controlling' => 'Landesamt für Soziales und Versorgung, Überwachungsstelle Barrierefreies Internet',
                'controlling_email' => 'Durchsetzung.BIT@MSGIV.Brandenburg.de',
                'controlling_url' => 'https://lasv.brandenburg.de/',
                'controlling_plz' => '14467',
                'controlling_city' => 'Potsdam',
                'controlling_street' => 'Henning-von-Tresckow-Straße 2-13',
            ],
            [
                'region' => __('Bremen', 'rrze-legal'),
                'url_law' => 'https://www.transparenz.bremen.de/sixcms/detail.php?gsid=bremen2014_tp.c.124514.de&asl=bremen203_tpgesetz.c.55340.de&template=20_gp_ifg_meta_detail_d#jlr-BGGBR2018pP12',
                'url_vo' => 'http://www.gesetze-im-internet.de/bitv_2_0/',
                'controlling' => 'Zentralstelle für barrierefreie Informationstechnik',
                'controlling_email' => 'ulrike.peter@lbb.bremen.de',
                'controlling_url' => 'https://www.behindertenbeauftragter.bremen.de/der-beauftragte/zentralstelle-fuer-barrierefreie-informationstechnik-28011',
                'controlling_plz' => '28199',
                'controlling_city' => 'Bremen',
                'controlling_street' => 'Teerhof 59',
            ],
            [
                'region' => __('Hamburg', 'rrze-legal'),
                'url_law' => 'http://www.landesrecht-hamburg.de/jportal/portal/page/bshaprod.psml?showdoccase=1&st=lr&doc.id=jlr-GleichstbMGHArahmen',
                'url_vo' => '',
                'controlling' => 'Die Überwachungsstelle für Barrierefreiheit von Informationstechnik der Freien und Hansestadt Hamburg',
                'controlling_email' => 'ueberwachungsstelle.barrierefreiheit@sk.hamburg.de',
                'controlling_url' => 'https://www.hamburg.de/ueberwachungsstelle-barrierefreiheit/',
                'controlling_address' => '',

            ],
            [
                'region' => __('Hessen', 'rrze-legal'),
                'url_law' => 'https://www.rv.hessenrecht.hessen.de/bshe/document/jlr-BGGHEV8IVZ',
                'url_vo' => '',
                'controlling' => 'Durchsetzungs- und Überwachungsstelle Barrierefreie Informationstechnik,  Hessisches Ministerium für Soziales und Integration',
                'controlling_email' => 'Durchsetzungsstelle-LBIT@rpgi.hessen.de',
                'controlling_url' => 'https://soziales.hessen.de/ueber-uns/beauftragte-fuer-barrierefreie-it/aufgaben-der-landesbeauftragten-fuer-barrierefreie-it',
                'controlling_plz' => '35390',
                'controlling_city' => 'Gießen',
                'controlling_street' => 'Landgraf-Philipp-Platz 1-7',
            ],
            [
                'region' => __('Mecklenburg-Vorpommern', 'rrze-legal'),
                'url_law' => 'http://www.landesrecht-mv.de/jportal/portal/page/bsmvprod.psml;jsessionid=0061262BA90EF14DA9B6664FD15E61B7.jp26?showdoccase=1&st=lr&doc.id=jlr-BGGMVrahmen&doc.part=X&doc.origin=bs',
                'url_vo' => 'https://www.regierung-mv.de/Landesregierung/sm/Soziales/Behinderungen/Das-Landesbehindertengleichstellungsgesetz-und-seine-Rechtsverordnungen',
                'controlling' => 'Überwachungsstelle Mecklenburg-Vorpommern',
                'controlling_email' => 'ueberwachungsstelle@sm.mv-regierung.de',
                'controlling_url' => 'https://www.regierung-mv.de/Landesregierung/sm/Soziales/Ueberwachungsstelle/',
                'controlling_plz' => '19055',
                'controlling_city' => 'Schwerin',
                'controlling_street' => 'Werderstraße 124',
            ],
            [
                'region' => __('Niedersachsen', 'rrze-legal'),
                'url_law' => 'http://www.voris.niedersachsen.de/jportal/?quelle=jlink&query=BehGleichG+ND&psml=bsvorisprod.psml&max=true&aiz=true',
                'url_vo' => '',
                'controlling' => 'Barrierefreie IT in Niedersachsen',
                'controlling_email' => 'schlichtungsstelle@ms.niedersachsen.de',
                'controlling_url' => 'https://www.ms.niedersachsen.de/startseite/service_kontakt/barrierefreie_it/barrierefreie-it-in-niedersachsen-183088.html',
                'controlling_address' => '',
            ],
            [
                'region' => __('Nordrhein-Westfalen', 'rrze-legal'),
                'url_law' => 'http://recht.nrw.de/lmi/owa/br_bes_text?anw_nr=2&gld_nr=2&ugl_nr=201&bes_id=5216&aufgehoben=N&menu=1&sg=0#det190773',
                'url_vo' => 'https://recht.nrw.de/lmi/owa/br_vbl_detail_text?anw_nr=6&vd_id=17834&ver=8&val=17834&sg=0&menu=1&vd_back=N',
                'controlling' => 'Überwachungsstelle für barrierefreie Informationstechnik des Landes Nordrhein-Westfalen',
                'controlling_email' => 'ueberwachungsstelle-nrw@it.nrw.de',
                'controlling_url' => 'https://www.mags.nrw/ueberwachungsstelle-barrierefreie-informationstechnik',
                'controlling_address' => '',
            ],
            [
                'region' => __('Rheinland-Pfalz', 'rrze-legal'),
                'url_law' => 'http://landesrecht.rlp.de/jportal/portal/t/im6/page/bsrlpprod.psml;jsessionid=9ED11D4B99D0BC1B86F507A116B67B2F.jp25?pid=Dokumentanzeige&showdoccase=1&js_peid=Trefferliste&documentnumber=1&numberofresults=1&fromdoctodoc=yes&doc.id=jlr-BehGleichGRPrahmen&doc.part=X&doc.price=0.0#focuspoint',
                'url_vo' => '',
                'controlling' => 'Überwachungsstelle für barrierefreie Informationstechnik',
                'controlling_email' => 'IT-Barrierefreiheit@lfst.fin-rlp.de',
                'controlling_url' => 'https://www.lfst-rlp.de/startseite/ueberwachungsstelle-fuer-barrierefreie-informationstechnik',
                'controlling_plz' => '56073',
                'controlling_city' => 'Koblenz',
                'controlling_street' => 'Ferdinand-Sauerbruch-Str. 17',
            ],
            [
                'region' => __('Saarland', 'rrze-legal'),
                'url_law' => '',
                'url_vo' => 'http://sl.juris.de/cgi-bin/landesrecht.py?d=http://sl.juris.de/sl/gesamt/SBGV_SL_2006.htm#SBGV_SL_2006_rahmen',
                'controlling' => 'Schlichtungsstelle, Ministerium für Soziales, Gesundheit, Frauen und Familie - Ref. B1',
                'controlling_email' => 'inklusion@soziales.saarland.de',
                'controlling_url' => '',
                'controlling_plz' => '66119',
                'controlling_city' => 'Saarbrücken',
                'controlling_street' => 'Franz-Josef-Röder-Straße 23',
            ],
            [
                'region' => __('Sachsen', 'rrze-legal'),
                'url_law' => 'https://www.revosax.sachsen.de/vorschrift/18283-Saechsisches-Inklusionsgesetz#p9',
                'url_vo' => 'https://www.revosax.sachsen.de/vorschrift/18133-Barrierefreie-Websites-Gesetz',
                'controlling' => 'Überwachungsstelle in Sachsen',
                'controlling_email' => 'bfit-sachsen@dzblesen.de',
                'controlling_url' => 'https://www.dzblesen.de/ueber-uns/fachthemen-kooperationen-projekte/ueberwachungsstelle-in-sachsen',
                'controlling_address' => '',
            ],
            [
                'region' => __('Sachsen-Anhalt', 'rrze-legal'),
                'url_law' => 'http://www.landesrecht.sachsen-anhalt.de/jportal/?quelle=jlink&query=BehGleichG+ST&psml=bssahprod.psml&max=true',
                'url_vo' => '',
                'controlling' => 'Beauftragter der Sächsischen Staatsregierung für die Belange von Menschen mit Behinderungen',
                'controlling_email' => 'info.behindertenbeauftragter@sk.sachsen.de',
                'controlling_url' => '',
                'controlling_plz' => '01097',
                'controlling_city' => 'Dresden',
                'controlling_street' => 'Archivstraße 1',
            ],
            [
                'region' => __('Schleswig-Holstein', 'rrze-legal'),
                'url_law' => 'http://www.gesetze-rechtsprechung.sh.juris.de/jportal/?quelle=jlink&query=BGG+SH&psml=bsshoprod.psml&max=true',
                'url_vo' => '',
                'controlling' => 'Beschwerdestelle für barrierefreie Informationstechnik',
                'controlling_email' => 'bbit@landtag.ltsh.de',
                'controlling_url' => 'https://www.landtag.ltsh.de/beauftragte/beschwerdestelle-fuer-barrieren/',
                'controlling_plz' => '24105',
                'controlling_city' => 'Kiel',
                'controlling_street' => 'Karolinenweg 1',
            ],
            [
                'region' => __('Thüringen', 'rrze-legal'),
                'url_law' => 'http://landesrecht.thueringen.de/jportal/portal/t/ps9/page/bsthueprod.psml;jsessionid=FBEDF07ACA45BF60CF8E5C576567D539.jp27?pid=Dokumentanzeige&showdoccase=1&js_peid=Trefferliste&documentnumber=1&numberofresults=1&fromdoctodoc=yes&doc.id=jlr-BfWebGTHrahmen&doc.part=X&doc.price=0.0#focuspoint',
                'url_vo' => 'http://landesrecht.thueringen.de/jportal/?quelle=jlink&query=BITV+TH&psml=bsthueprod.psml&max=true&aiz=true',
                'controlling' => 'Zentrale Überwachungsstelle digitale Barrierefreiheit',
                'controlling_email' => 'ueberwachung-digitale-barrierefreiheit@tfm.thueringen.de',
                'controlling_url' => 'https://finanzen.thueringen.de/ministerium/zentrale-ueberwachungsstelle-digitale-barrierefreiheit',
                'controlling_plz' => '99096',
                'controlling_city' => 'Erfurt',
                'controlling_street' => 'Jürgen-Fuchs-Straße 1',
            ]
        ];
    }
}
