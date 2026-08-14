<?php

namespace RRZE\Legal\TOS;

defined('ABSPATH') || exit;

use RRZE\Legal\{Locale, Template};
use function RRZE\Legal\{plugin, tos, consentCookies};

class Endpoint {
    protected static array $currentEndpointRequestData = [];

    /**
     * Class constructor.
     */
    public function __construct()  {
        add_action('init', [__CLASS__, 'addEndpoint']);
        add_filter('request', [__CLASS__, 'manualPageRequest'], 0);
        add_action('template_redirect', [__CLASS__, 'endpointTemplateRedirect']);
    }

    public static function slugsTitles()  {
        return [
            'imprint' => __('Imprint', 'rrze-legal'),
            'privacy' => __('Privacy', 'rrze-legal'),
            'accessibility' => __('Accessibility', 'rrze-legal'),
        ];
    }

    public static function defaultSlugs() {
        return [
            'imprint' => 'imprint',
            'privacy' => 'privacy',
            'accessibility' => 'accessibility',
        ];
    }

    public static function avalaibleI18nSlugs()  {
        return [
            'de' => [
                'imprint' => 'impressum',
                'privacy' => 'datenschutz',
                'accessibility' => 'barrierefreiheit',
            ],
            'en' => self::defaultSlugs(),
        ];
    }

    public static function getSlugs()  {
        $langCode = Locale::getLangCode();
        return in_array($langCode, array_keys(self::avalaibleI18nSlugs())) ? self::avalaibleI18nSlugs()[$langCode] : self::defaultSlugs();
    }

    public static function addEndpoint()  {
        foreach (self::avalaibleI18nSlugs() as $slugs) {
            foreach ($slugs as $slug) {
                add_rewrite_endpoint($slug, EP_ROOT);
            }
        }
    }

    public static function manualPageRequest(array $queryVars): array {
        $requestData = self::getRootEndpointRequestData();
        if (empty($requestData)) {
            return $queryVars;
        }

        $page = self::getAllowedManualPage($requestData['endpoint'], $requestData['page_path']);
        if (!($page instanceof \WP_Post)) {
            return $queryVars;
        }

        return [
            'page_id' => $page->ID,
        ];
    }

    public static function endpointTemplateRedirect()  {
        $langCode = Locale::getLangCode();
        $requestData = self::getRootEndpointRequestData();

        if (empty($requestData)) {
            return;
        }

        if (self::getAllowedManualPage($requestData['endpoint'], $requestData['page_path']) instanceof \WP_Post) {
            return;
        }

        self::$currentEndpointRequestData = $requestData;
        add_action('admin_bar_menu', [__CLASS__, 'adminBarEditLink'], 80);
        add_filter('pre_get_document_title', [__CLASS__, 'documentTitle']);

        // Get the options
        $options = tos()->getOptions();
        // Adjustments
        $value = $options['imprint_scope_websites'];
        if (!is_array($value)) {
            $value = explode(PHP_EOL, $value);
        }
        array_unshift($value, tos()->getSiteUrlHost());
        $value = array_map('trim', $value);
        $value = array_filter($value);
        $value = array_unique($value);
        $value = array_map('esc_html', $value);
        if (count($value) > 1) {
            $lastValue = array_pop($value);
            $value = sprintf('%s %s %s', implode(', ', $value), __('and', 'rrze-legal'), $lastValue);
            $options['imprint_websites_extra'] = '1';
        } else {
            $value = implode('', $value);
        }

        $options['imprint_scope_websites'] = $value;
        // Set default domain option
        $options['is_default_domain'] = tos()->isCurrentSiteInDefaultDomains() ? '1' : '0';

   
        // Set accessibility conformity
        self::setAccessibilityConformity($options);
        // Set accessibility compliance method
        self::setComplianceMethod($options);
        // Set accessibility compliance dates
        self::setComplianceDates($options);
        // Set accessibility compliance content list
        self::setComplianceContentList($options);
        
        // Check if ID Numbers are present and set imprint_id_numbers_exists to true if so.
        self::setFilledIDNumbers($options);    
        // Check if name, email, url or phone for IT Security exists
        self::setFilledAbuseData($options);
        // Check if Supervisory data exists
        self:: setFilledSupervisory($options);
        
        
        // Set contact form
        self::setContactForm($options);

        // Get the parent template
        $template = plugin()->getPath(Template::TOS_PATH) . $requestData['endpoint'] . '-' . $langCode . '.html';
        if (!is_readable($template)) {
            $template = plugin()->getPath(Template::TOS_PATH) . $requestData['endpoint'] . '-en.html';
        }
        if (!is_readable($template)) {
            self::error404();
        }
        // Find child templates in settings fields
        foreach (tos()->getFields() as $key => $field) {
            $_tplAry = isset($field['template']) && is_array($field['template']) ? $field['template'] : [];
            foreach ($_tplAry as $_val => $_tpl) {
                if ($options[$key] == $_val) {
                    $tpl = plugin()->getPath(Template::TOS_PATH) . $_tpl . '-' . $langCode . '.html';
                    if (!is_readable($tpl)) {
                        $tpl = plugin()->getPath(Template::TOS_PATH) . $_tpl . '-en.html';
                    }
                    $options[str_replace('-', '_', $_tpl) . '_template'] = is_readable($tpl) ? self::getContent($tpl, $options) : '';
                } elseif ($_tpl) {
                    $options[str_replace('-', '_', $_tpl) . '_template'] = '';
                }
            }
        }
        self::setPrivacyTechnicalServices($options);
        // Includes service providers.
        $options['privacy_external_service_providers'] = '';
        $options['service_providers_template'] = [];
        $consentCookiesOptions = consentCookies()->getOptions();
        foreach (tos()->getServiceProvidersStatus() as $key => $value) {
            if (empty($value) || empty($consentCookiesOptions[$key])) {
                continue;
            }
            if (
                consentCookies()->hasPluginDependency($consentCookiesOptions[$key]) &&
                !consentCookies()->isPluginDependencyActive($consentCookiesOptions[$key])
            ) {
                continue;
            }
            $options['service_providers_template'][$key] = self::getServiceProviderContent($key, $consentCookiesOptions[$key], $langCode);
        }
        if (!empty($options['service_providers_template'])) {
            $options['privacy_external_service_providers'] = '1';
        }
        // Includes other child templates
       
        $default_active_subtemplates = ['imprint-representation', 'imprint-id-numbers', 'imprint-supervisory-authority', 'imprint-it-security', 'imprint-whistleblower-system', 'privacy-controller', 'privacy-dpo', 'privacy-general-rightsdata', 'privacy-supervisory-authority', 'privacy-general', 'privacy-technical-server', 'privacy-technical-cookies', 'privacy-technical-javascript', 'privacy-technical-email', 'privacy-closingstatement', 'accessibility-supervisory-authority'];
        
        foreach ($default_active_subtemplates as $_tpl) {
            $tpl = plugin()->getPath(Template::TOS_PATH) . $_tpl . '-' . $langCode . '.html';
            if (!is_readable($tpl)) {
                $tpl = plugin()->getPath(Template::TOS_PATH) . $_tpl . '-en.html';
            }
            $options[str_replace('-', '_', $_tpl) . '_template'] = is_readable($tpl) ? self::getContent($tpl, $options) : '';
        }
        
        
        
        // Render all templates and get the page content
        $content = self::getContent($template, $options);
        // Search content for shortcodes and filter shortcodes through their hooks
        // Shortcodes inside HTML elements will be skipped
        $content = do_shortcode($content);
        self::enqueueFrontendStyle();
        $content = self::wrapEndpointContent($content);
        $title = $requestData['title'] ?? '';
        // Render the page with the content
        $template = plugin()->getPath(Template::THEMES_PATH) . Template::getThemeFilename();
        if (!is_readable($template)) {
            self::error404();
        }
        include($template);
        exit;
    }

    protected static function wrapEndpointContent(string $content): string
    {
        return '<div class="rrze-legal">' . $content . '</div>';
    }

    protected static function getServiceProviderContent(string $key, array $data, string $langCode): string
    {
        $name = wp_strip_all_tags((string) ($data['name'] ?? $key));
        $id = sanitize_key((string) ($data['id'] ?? $key));
        $text = self::getLocalizedServiceProviderText($data, $langCode);

        $content = sprintf('<h3>%s</h3>', esc_html($name)) . "\n";
        $content .= self::formatPlainTextParagraphs($text);
        $content .= self::getServiceProviderCookieInformation($data, $langCode);
        $content .= self::getServiceProviderConsentSwitch($id, $langCode);

        return $content;
    }

    protected static function setPrivacyTechnicalServices(array &$options): void
    {
        $templates = [
            'privacy_technical_newsletter' => 'privacy_technical_newsletter_template',
            'privacy_technical_contactforms' => 'privacy_technical_contactforms_template',
            'privacy_technical_registrationforms' => 'privacy_technical_registrationforms_template',
        ];

        $options['privacy_technical_services'] = '';
        foreach ($templates as $flag => $template) {
            $options[$flag] = '';
            if (trim((string) ($options[$template] ?? '')) !== '') {
                $options[$flag] = '1';
                $options['privacy_technical_services'] = '1';
            }
        }
    }

    protected static function getLocalizedServiceProviderText(array $data, string $langCode): string
    {
        $primaryKey = $langCode === 'de' ? 'privacy_text_de' : 'privacy_text_en';
        $fallbackKey = $langCode === 'de' ? 'privacy_text_en' : 'privacy_text_de';
        $text = (string) ($data[$primaryKey] ?? '');
        if ($text === '') {
            $text = (string) ($data[$fallbackKey] ?? '');
        }
        if ($text === '') {
            $text = (string) ($data['purpose'] ?? '');
        }
        return $text;
    }

    protected static function formatPlainTextParagraphs(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", trim($text));
        if ($text === '') {
            return '';
        }

        $paragraphs = preg_split("/\n{2,}/", $text);
        $content = '';
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if ($paragraph === '') {
                continue;
            }
            $content .= '<p>' . nl2br(esc_html($paragraph), false) . '</p>' . "\n";
        }
        return $content;
    }

    protected static function getServiceProviderCookieInformation(array $data, string $langCode): string
    {
        $labels = self::getServiceProviderLabels($langCode);
        $items = [
            $labels['provider'] => esc_html((string) ($data['provider'] ?? '')),
            $labels['privacy_policy_url'] => self::formatServiceProviderPrivacyUrl($data['privacy_policy_url'] ?? ''),
            $labels['hosts'] => self::formatServiceProviderList($data['hosts'] ?? ''),
            $labels['cookie_name'] => esc_html((string) ($data['cookie_name'] ?? '')),
            $labels['cookie_expiry'] => esc_html((string) ($data['cookie_expiry'] ?? '')),
        ];

        $content = "\n" . sprintf('<h4>%s</h4>', esc_html($labels['cookie_information'])) . "\n";
        $content .= '<table class="rrze-legal-service-provider-cookie-information">' . "\n<tbody>\n";
        foreach ($items as $label => $value) {
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }
            $content .= sprintf(
                '<tr><th scope="row">%1$s</th><td>%2$s</td></tr>',
                esc_html($label),
                $value
            ) . "\n";
        }
        $content .= "</tbody>\n</table>\n";

        return $content;
    }

    protected static function formatServiceProviderPrivacyUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        return sprintf(
            '<a href="%1$s" rel="noopener noreferrer">%2$s</a>',
            esc_url($url),
            esc_html($url)
        );
    }

    protected static function formatServiceProviderList($value): string
    {
        if (is_array($value)) {
            $items = $value;
        } else {
            $items = preg_split('/[\r\n,]+/', (string) $value);
        }

        $items = array_map('trim', $items);
        $items = array_filter($items);
        $items = array_map('esc_html', $items);

        return implode(', ', $items);
    }

    protected static function getServiceProviderConsentSwitch(string $id, string $langCode): string
    {
        $text = __('You can change your consent at any time using the following option.', 'rrze-legal');

        return sprintf(
            "<h4>%1\$s</h4>\n<p>%2\$s</p>\n[rrzelegal_consent type=\"switch-consent\" id=\"%3\$s\"]\n",
            esc_html__('Objection and removal option', 'rrze-legal'),
            esc_html($text),
            esc_attr($id)
        );
    }

    protected static function getServiceProviderLabels(string $langCode): array
    {
        if ($langCode === 'de') {
            return [
                'cookie_information' => __('Cookie-Informationen', 'rrze-legal'),
                'provider' => __('Provider', 'rrze-legal'),
                'privacy_policy_url' => __('Privacy policy', 'rrze-legal'),
                'hosts' => __('Hosts', 'rrze-legal'),
                'cookie_name' => __('Cookie-Name', 'rrze-legal'),
                'cookie_expiry' => __('Cookie-Laufzeit', 'rrze-legal'),
            ];
        }

        return [
            'cookie_information' => __('Cookie information', 'rrze-legal'),
            'provider' => __('Provider', 'rrze-legal'),
            'privacy_policy_url' => __('Privacy policy', 'rrze-legal'),
            'hosts' => __('Hosts', 'rrze-legal'),
            'cookie_name' => __('Cookie name', 'rrze-legal'),
            'cookie_expiry' => __('Cookie expiry', 'rrze-legal'),
        ];
    }

    public static function adminBarEditLink(\WP_Admin_Bar $wpAdminBar): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        if (empty(self::$currentEndpointRequestData['endpoint'])) {
            return;
        }

        $endpoint = self::$currentEndpointRequestData['endpoint'];
        $href = add_query_arg(
            [
                'page' => 'legal',
                'current-tab' => tos()->getPagePrefix() . $endpoint,
            ],
            admin_url('admin.php')
        );

        $wpAdminBar->add_node(
            [
                'id' => 'edit',
                'title' => __('Edit', 'rrze-legal'),
                'href' => $href,
                'meta' => [
                    'title' => __('Edit legal settings', 'rrze-legal'),
                ],
            ]
        );
    }

    public static function documentTitle(): string {
        $requestData = self::getRootEndpointRequestData();
        return $requestData['title'] ?? '';
    }

    protected static function getRootEndpointRequestData(): array {
        $locale = Locale::getLocale();
        $defaultLocale = Locale::getDefaultLocale();
        $langCode = Locale::getLangCode();
        $urlSlug = self::getRootEndpointSlug();

        foreach (self::avalaibleI18nSlugs() as $lang => $slugs) {
            foreach ($slugs as $key => $slug) {
                if ($urlSlug == $slug && $langCode != $lang) {
                    $redirectSlug = self::avalaibleI18nSlugs()[$langCode][$key];
                    $langSegment = $defaultLocale != $locale ? $langCode . '/' : '';
                    wp_redirect(site_url($langSegment . $redirectSlug));
                    exit;
                }
                if ($urlSlug == $slug && $langCode == $lang) {
                    return [
                        'page_path' => $slug,
                        'title' => self::slugsTitles()[$key],
                        'endpoint' => self::defaultSlugs()[$key],
                    ];
                }
            }
        }

        return [];
    }

    protected static function getRootEndpointSlug(): string {
        global $wp;

        $request = isset($wp->request) ? trim((string) $wp->request, '/') : '';
        if ($request === '') {
            return '';
        }

        $segments = explode('/', $request);
        if (count($segments) === 1) {
            return $segments[0];
        }

        $locale = Locale::getLocale();
        $defaultLocale = Locale::getDefaultLocale();
        $langCode = Locale::getLangCode();

        if ($defaultLocale !== $locale && count($segments) === 2 && $segments[0] === $langCode) {
            return $segments[1];
        }

        return '';
    }

    protected static function getAllowedManualPage(string $endpoint, string $pagePath): ?\WP_Post {
        if (!tos()->isManualPageAllowed($endpoint)) {
            return null;
        }

        $page = get_page_by_path($pagePath, OBJECT, 'page');
        if (!($page instanceof \WP_Post)) {
            return null;
        }

        if ($page->post_status !== 'publish') {
            return null;
        }

        return $page;
    }

    protected static function setContactForm(&$options)  {
        $contactForm = new ContactForm();
        $options['accessibility_contact_form'] = $contactForm->setForm();
    }

    protected static function setAccessibilityConformity(&$options)  {
        $key = $options['accessibility_compliance_status_conformity'] ?? '';
        $fields = (array) tos()->getFields()['accessibility_compliance_status_conformity'] ?? [];
        $optionsField = $fields['options'] ?? [];
        $styleField = $fields['style'] ?? [];
        $complianceField = $fields['compliance'] ?? [];
        $options['accessibility_conformity_label'] = $optionsField[$key];
        $options['accessibility_conformity_filled'] = $complianceField[$key] ? 1 : 0;
        $options['accessibility_conformity_alert_style'] = $styleField[$key];
    }

     protected static function setFilledIDNumbers(&$options)  {
         $filled = false;
         
        if (!empty($options['imprint_id_numbers_ustg'])
            || (!empty($options['imprint_id_numbers_tax']))
            || (!empty($options['imprint_id_numbers_duns']))
            || (!empty($options['imprint_id_numbers_eori']))
            || (!empty($options['imprint_id_numbers_iban']))
            || (!empty($options['imprint_id_numbers_bic'])) ) {
            $filled = true;
        }
            
        if ((!empty($options['imprint_id_numbers_kontoinhaber']))
            && (!empty($options['imprint_id_numbers_iban']))
            && (!empty($options['imprint_id_numbers_bic']))) {
            $options['imprint_id_numbers_bankdata'] = true;
        }
        
       $options['imprint_id_numbers_exists'] = $filled;

    }
    protected static function setFilledAbuseData(&$options)  {
         $filled = false;
         
        if (!empty($options['imprint_it_security_name'])
            || (!empty($options['imprint_it_security_email']))
             || (!empty($options['imprint_it_security_url']))
            || (!empty($options['imprint_it_security_phone']))) {
            $filled = true;
        }
        if (!empty($options['imprint_it_security_postal_co'])
            || (!empty($options['imprint_it_security_postal_street']))
            || (!empty($options['imprint_it_security_postal_city']))) {
            $options['imprint_it_security_address'] = true;
        }
        
       $options['imprint_it_security_data'] = $filled;

    }
     protected static function setFilledSupervisory(&$options)  {
         $filled = false;
         
        if (!empty($options['imprint_supervisory_authority_name'])
            || (!empty($options['imprint_supervisory_authority_postal_street']))
             || (!empty($options['imprint_supervisory_authority_postal_code']))
            || (!empty($options['imprint_supervisory_authority_postal_city']))) {
            $filled = true;
        }
      
        
       $options['imprint_supervisory_authority_data'] = $filled;

    }
    

    
    
    protected static function setComplianceMethod(&$options)  {
        $key = $options['accessibility_compliance_status_method'] ?? '';
        $fields = (array) tos()->getFields()['accessibility_compliance_status_method'] ?? [];
        $optionsField = $fields['options'] ?? [];
        $options['accessibility_compliance_method_label'] =  $optionsField[$key];
    }

    protected static function setComplianceDates(&$options)  {
        $creationDate = $options['accessibility_compliance_status_creation_date'] ?? '';
        $options['accessibility_compliance_status_creation_date'] = $creationDate ? date_i18n(get_option('date_format'), strtotime($creationDate)) : '';
        $lastReviewDate = $options['accessibility_compliance_status_last_review_date'] ?? '';
        $options['accessibility_compliance_status_last_review_date'] = $lastReviewDate ? date_i18n(get_option('date_format'), strtotime($lastReviewDate)) : '';
    }

    protected static function setComplianceContentList(&$options)  {
        $contentHelper = $options['accessibility_statement_non_accessible_content_helper'] ?? '';
        $contentList = (array) $options['accessibility_statement_non_accessible_content_list'] ?? [];
        if ($contentHelper == '0' && !empty($contentList)) {
            $list = '';
            $fields = (array) tos()->getFields()['accessibility_statement_non_accessible_content_list'] ?? [];
            $optionsField = $fields['options'] ?? [];
            foreach (array_keys($contentList) as $key) {
                if (isset($optionsField[$key])) {
                    $list .= '<li>' . $optionsField[$key] . '</li>';
                }
            }
            $options['accessibility_non_accessible_content_list'] = $list ? '<ul>' . $list . '</ul>' : '';
        }
    }

    protected static function getContent($template, $options = []) {
        $content = Template::getContent($template, $options);
        $content = preg_replace('/(^|[^\n\r])[\r\n](?![\n\r])/', '$1 ', $content);
        return $content;
    }

    protected static function enqueueFrontendStyle(): void
    {
        $handle = 'rrze-legal-frontend';

        wp_enqueue_style(
            $handle,
            plugin()->getUrl('build') . 'rrze-legal.css',
            [],
            plugin()->getVersion()
        );
    }

    protected static function error404()
    {
        if ($template = locate_template('404.php')) {
            load_template($template);
            exit;
        } else {
            wp_die(esc_html(__('Not Found', 'rrze-legal')), 404);
        }
    }

    public static function endpointTitle(string $slug = ''): string
    {
        $defaultSlugs = self::defaultSlugs();
        if (!isset($defaultSlugs[$slug])) {
            return '';
        }
        $slugsTitles = self::slugsTitles();
        return $slugsTitles[$slug];
    }

    public static function endpointUrl(string $slug = ''): string
    {
        $defaultSlugs = self::defaultSlugs();
        if (!isset($defaultSlugs[$slug])) {
            return '';
        }
        $locale = Locale::getLocale();
        $defaultLocale = Locale::getDefaultLocale();
        $langCode = Locale::getLangCode();
        $slugs = self::getSlugs();
        $langSegment = $locale != $defaultLocale ? $langCode . '/' : '';
        return site_url($langSegment . $slugs[$slug] . '/');
    }

    public static function endpointLink(string $slug = ''): string
    {
        $defaultSlugs = self::defaultSlugs();
        if (!isset($defaultSlugs[$slug])) {
            return '';
        }
        $slugsTitles = self::slugsTitles();
        return sprintf(
            '<a href="%s">%s</a>',
            self::endpointUrl($slug),
            $slugsTitles[$slug]
        );
    }
}
