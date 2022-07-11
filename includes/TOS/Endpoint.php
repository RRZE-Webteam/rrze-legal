<?php

namespace RRZE\Legal\TOS;

defined('ABSPATH') || exit;

use RRZE\Legal\{Locale, Template};
use function RRZE\Legal\{plugin, tos};

class Endpoint
{
    /**
     * Class constructor.
     */
    public function __construct()
    {
        add_action('init', [__CLASS__, 'addEndpoint']);
        add_action('template_redirect', [__CLASS__, 'endpointTemplateRedirect'], 9999);
    }

    public static function slugsTitles()
    {
        return [
            'imprint' => __('Imprint', 'rrze-legal'),
            'privacy' => __('Privacy', 'rrze-legal'),
            'accessibility' => __('Accessibility', 'rrze-legal'),
        ];
    }

    public static function defaultSlugs()
    {
        return [
            'imprint' => 'imprint',
            'privacy' => 'privacy',
            'accessibility' => 'accessibility',
        ];
    }

    public static function avalaibleI18nSlugs()
    {
        return [
            'de' => [
                'imprint' => 'impressum',
                'privacy' => 'datenschutz',
                'accessibility' => 'barrierefreiheit',
            ],
            'en' => self::defaultSlugs(),
        ];
    }

    public static function getSlugs()
    {
        $langCode = is_user_logged_in() && is_admin() ? Locale::getUserLangCode() : Locale::getLangCode();
        return in_array($langCode, array_keys(self::avalaibleI18nSlugs())) ? self::avalaibleI18nSlugs()[$langCode] : self::defaultSlugs();
    }

    public static function addEndpoint()
    {
        foreach (self::avalaibleI18nSlugs() as $slugs) {
            foreach ($slugs as $slug) {
                add_rewrite_endpoint($slug, EP_ROOT);
            }
        }
    }

    public static function endpointTemplateRedirect()
    {
        $title = '';
        $prefix = '';
        $locale = Locale::getLocale();
        $defaultLocale = Locale::getDefaultLocale();
        $langCode = is_user_logged_in() && is_admin() ? Locale::getUserLangCode() : Locale::getLangCode();

        global $wp;
        $url = site_url($wp->request);
        $segments = explode('/', $url);
        $urlSlug = $segments[array_key_last($segments)];

        foreach (self::avalaibleI18nSlugs() as $lang => $slugs) {
            foreach ($slugs as $key => $slug) {
                if ($urlSlug == $slug && $langCode != $lang) {
                    $r = self::avalaibleI18nSlugs()[$langCode][$key];
                    $langSegment = $defaultLocale != $locale ? $langCode . '/' : '';
                    wp_redirect(site_url($langSegment . $r));
                    exit;
                } elseif ($urlSlug == $slug && $langCode == $lang) {
                    $title = self::slugsTitles()[$key];
                    $prefix = self::defaultSlugs()[$key];
                    break 2;
                }
            }
        }
        if (!$title || !$prefix) {
            return;
        }
        $page = get_page_by_title($title);
        if (!is_null($page) && $page->post_status == 'publish') {
            // Replaces double line breaks with paragraph elements
            $content = wpautop($page->post_content);
            // Search content for shortcodes and filter shortcodes through their hooks
            // Shortcodes inside HTML elements will be skipped
            $content = do_shortcode($content);
            // Render the page with the content
            $template = plugin()->getPath(Template::THEMES_PATH) . Template::getThemeFilename();
            include($template);
            exit;
        }
        // Get the options
        $options = tos()->getOptions();
        // Adjustments
        $value = $options['imprint_scope_websites'];
        if (!is_array($value)) {
            $value = explode(PHP_EOL, $value);
        }
        $lastValue = array_pop($value);
        $value = sprintf('%s %s %s', implode(', ', $value), __('and', 'rrze-legal'), $lastValue);
        $options['imprint_scope_websites'] = $value;
        // Set default domain option
        $options['is_default_domain'] = tos()->isCurrentSiteInDefaultDomains() ? '1' : '0';
        // Legal area
        $legalArea = $options['accessibility_general_legal_area'] ?? '';
        foreach (tos()->getLegalAreaData() as $key => $area) {
            if ($legalArea == $key) {
                foreach ($area as $_key => $_value) {
                    $options['accessibility_' . $_key] = $_value;
                }
                break;
            }
        }
        // Set accessibility conformity
        self::setAccessibilityConformity($options);
        // Set accessibility compliance method
        self::setComplianceMethod($options);
        // Set accessibility compliance dates
        self::setComplianceDates($options);
        // Set accessibility compliance content list
        self::setComplianceContentList($options);
        // Set contact form
        self::setContactForm($options);

        // Get the parent template
        $template = plugin()->getPath(Template::TOS_PATH) . $prefix . '-' . $langCode . '.html';
        if (!is_readable($template)) {
            $template = plugin()->getPath(Template::TOS_PATH) . $prefix . '-en.html';
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
        // Includes service providers templates
        $options['privacy_external_service_providers'] = '';
        $options['service_providers_template'] = [];
        foreach (tos()->getServiceProvidersStatus() as $key => $value) {
            if ($value) {
                $tpl = plugin()->getPath(Template::TOS_PATH) .
                    sprintf('service-providers/%1$s-cookie-%2$s.html', str_replace('_', '-', $key), $langCode);
                if (!is_readable($tpl)) {
                    $tpl = plugin()->getPath(Template::TOS_PATH) .
                        sprintf('service-providers/%s-cookie-en.html', str_replace('_', '-', $key));
                }
                if (is_readable($tpl)) {
                    $options['service_providers_template'][$key] = is_readable($tpl) ? self::getContent($tpl) : '';
                }
            }
        }
        if (!empty($options['service_providers_template'])) {
            $options['privacy_external_service_providers'] = '1';
        }
        // Includes other child templates
        $_tpl = 'privacy-dpo';
        $tpl = plugin()->getPath(Template::TOS_PATH) . $_tpl . '-' . $langCode . '.html';
        if (!is_readable($tpl)) {
            $tpl = plugin()->getPath(Template::TOS_PATH) . $_tpl . '-en.html';
        }
        $options[str_replace('-', '_', $_tpl) . '_template'] = is_readable($tpl) ? self::getContent($tpl, $options) : '';
        $_tpl = 'privacy-rights-data-subject';
        $tpl = plugin()->getPath(Template::TOS_PATH) . $_tpl . '-' . $langCode . '.html';
        if (!is_readable($tpl)) {
            $tpl = plugin()->getPath(Template::TOS_PATH) . $_tpl . '-en.html';
        }
        $options[str_replace('-', '_', $_tpl) . '_template'] = is_readable($tpl) ? self::getContent($tpl, $options) : '';
        // Render all templates and get the page content
        $content = self::getContent($template, $options);
        // Search content for shortcodes and filter shortcodes through their hooks
        // Shortcodes inside HTML elements will be skipped
        $content = do_shortcode($content);
        // Render the page with the content
        $template = plugin()->getPath(Template::THEMES_PATH) . Template::getThemeFilename();
        if (!is_readable($template)) {
            self::error404();
        }
        include($template);
        exit;
    }

    protected static function setContactForm(&$options)
    {
        $contactForm = new ContactForm();
        $options['accessibility_contact_form'] = $contactForm->setForm();
    }

    protected static function setAccessibilityConformity(&$options)
    {
        $key = $options['accessibility_compliance_status_conformity'] ?? '';
        $fields = (array) tos()->getFields()['accessibility_compliance_status_conformity'] ?? [];
        $optionsField = $fields['options'] ?? [];
        $styleField = $fields['style'] ?? [];
        $complianceField = $fields['compliance'] ?? [];
        $options['accessibility_conformity_label'] = $optionsField[$key];
        $options['accessibility_conformity_filled'] = $complianceField[$key] ? 1 : 0;
        $options['accessibility_conformity_alert_style'] = $styleField[$key];
    }

    protected static function setComplianceMethod(&$options)
    {
        $key = $options['accessibility_compliance_status_method'] ?? '';
        $fields = (array) tos()->getFields()['accessibility_compliance_status_conformity'] ?? [];
        $optionsField = $fields['options'] ?? [];
        $options['accessibility_compliance_method_label'] =  $optionsField[$key];
    }

    protected static function setComplianceDates(&$options)
    {
        $creationDate = $options['accessibility_compliance_status_creation_date'] ?? '';
        $options['accessibility_compliance_status_creation_date'] = $creationDate ? date_i18n(get_option('date_format'), strtotime($creationDate)) : '';
        $lastReviewDate = $options['accessibility_compliance_status_last_review_date'] ?? '';
        $options['accessibility_compliance_status_last_review_date'] = $lastReviewDate ? date_i18n(get_option('date_format'), strtotime($lastReviewDate)) : '';
    }

    protected static function setComplianceContentList(&$options)
    {
        $contentHelper = $options['accessibility_statement_non_accessible_content_helper'] ?? '';
        $contentList = (array) $options['accessibility_statement_non_accessible_content_list'] ?? [];
        if ($contentHelper == '0' && !empty($contentList)) {
            $list = '';
            $fields = (array) tos()->getFields()['accessibility_statement_non_accessible_content_list'] ?? [];
            $optionsField = $fields['options'] ?? [];
            foreach ($contentList as $key) {
                if (isset($optionsField[$key])) {
                    $list .= '<li>' . $optionsField[$key] . '</li>';
                }
            }
            $options['non_accessible_content_helper'] = $contentHelper;
            $options['accessibility_non_accessible_content_list'] = $list ? '<ul>' . $list . '</ul>' : '';
        }
    }

    protected static function getContent($template, $options = [])
    {
        $content = Template::getContent($template, $options);
        $content = preg_replace('/(^|[^\n\r])[\r\n](?![\n\r])/', '$1 ', $content);
        return $content;
    }

    protected static function error404()
    {
        if ($template = locate_template('404.php')) {
            load_template($template);
            exit;
        } else {
            wp_die(__('Not Found', 'rrze-legal'), 404);
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
        $langCode = is_user_logged_in() && is_admin() ? Locale::getUserLangCode() : Locale::getLangCode();
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
