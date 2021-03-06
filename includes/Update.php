<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

class Update
{
    /**
     * Execute on 'plugins_loaded' API/action.
     * @return void
     */
    public static function loaded()
    {
        $version = get_option(tos()->getOptionName() . '_version', '0');
        if (version_compare($version, '1.0.0', '<')) {
            self::updateToVersion100();
            Utils::redirectToReferer();
        } elseif (version_compare($version, '1.0.0', '==')) {
            self::updateToVersion200();
            Utils::redirectToReferer();
        } elseif (version_compare($version, '2.0.0', '==')) {
            self::updateToVersion220();
            Utils::redirectToReferer();
        }
    }

    /**
     * Update to version 1.0.0.
     * @return void
     */
    protected static function updateToVersion100()
    {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        $tosOptions = (array) get_option('rrze_tos');
        if (!empty($tosOptions)) {
            $options = tos()->getOptions();
            foreach (self::optionsMap100() as $tos => $legal) {
                if (isset($tosOptions[$tos]) && isset($options[$legal])) {
                    $options[$legal] = $tosOptions[$tos];
                }
            }
            update_option(tos()->getOptionName(), $options);
            if (
                Utils::isPluginActive('rrze-multilang/rrze-multilang.php')
                && get_option('rrze_legal_en') === false
            ) {
                update_option('rrze_legal_en', $options);
            }
        }
        update_option(tos()->getOptionName() . '_version', '1.0.0');
        if (
            Utils::isPluginActive('rrze-multilang/rrze-multilang.php')
            && get_option('rrze_legal_en_version') === false
        ) {
            update_option('rrze_legal_en_version', '1.0.0');
        }
    }

    /**
     * Update to version 2.0.0.
     * @return void
     */
    protected static function updateToVersion200()
    {
        $tosOptionName = tos()->getOptionName();
        $tosOptions = tos()->getOptions();

        foreach (self::optionsMap200() as $key => $newKey) {
            if (isset($tosOptions[$key])) {
                $status = $tosOptions[$key] ? '1' : '0';
                $tosOptions['privacy_service_providers'][$newKey] = $status;
            }
        }
        update_option($tosOptionName, $tosOptions);
        update_option($tosOptionName . '_version', '2.0.0');

        $cookiesOptionsName = consentCookies()->getOptionName();
        $cookiesOptions = consentCookies()->getOptions();
        $externalServices = $tosOptions['privacy_service_providers'];

        foreach ($cookiesOptions as $key => $value) {
            $category = $value['category'] ?? '';
            if ($category === 'essential') {
                continue;
            }
            if (!empty($externalServices[$key])) {
                $cookiesOptions[$key]['status'] = '1';
            } else {
                $cookiesOptions[$key]['status'] = '0';
            }
        }
        update_option($cookiesOptionsName, $cookiesOptions);
    }

    /**
     * Update to version 2.2.0.
     * @return void
     */
    protected static function updateToVersion220()
    {
        $tosOptionName = tos()->getOptionName();
        $cookiesOptionsName = consentCookies()->getOptionName();
        $cookiesOptions = consentCookies()->getOptions();

        foreach ($cookiesOptions as $key => $value) {
            $id = $value['id'] ?? '';
            $category = $value['category'] ?? '';
            if ($category === 'external_media' && $id === 'instagram') {
                unset($cookiesOptions[$key]);
                break;
            }
        }
        update_option($cookiesOptionsName, $cookiesOptions);
        update_option($tosOptionName . '_version', '2.2.0');
    }

    /**
     * Options map (v1.0.0).
     * @return array
     */
    protected static function optionsMap100()
    {
        return [
            'imprint_websites' => 'imprint_scope_websites',
            'imprint_webmaster_email' => 'imprint_webmaster_email',
            'accessibility_feedback_email' => 'accessibility_feedback_contact_email',
            'accessibility_region' => 'accessibility_general_legal_area',
            'accessibility_conformity_val' => 'accessibility_compliance_status_conformity',
            'accessibility_feedback_subject' => 'accessibility_feedback_email_subject',
            'display_template_supervisory' => 'imprint_optional_supervisory_authority',
            'display_template_idnumbers' => 'imprint_optional_id_numbers',
            'display_template_itsec' => 'imprint_optional_it_security',
            'display_template_vertretung' => 'imprint_optional_representation',
            'display_template_coronakontaktverfolgung' => 'privacy_services_corona_contact_tracking',
            'imprint_responsible_org' => 'imprint_responsible_person_organization',
            'imprint_responsible_name' => 'imprint_responsible_person_name',
            'imprint_responsible_email' => 'imprint_responsible_person_email',
            'imprint_responsible_street' => 'imprint_responsible_person_street',
            'imprint_responsible_postalcode' => 'imprint_responsible_person_postal_code',
            'imprint_responsible_city' => 'imprint_responsible_person_city',
            'imprint_responsible_phone' => 'imprint_responsible_person_phone',
            'imprint_responsible_fax' => 'imprint_responsible_person_fax',
            'imprint_webmaster_name' => 'imprint_webmaster_name',
            'imprint_webmaster_phone' => 'imprint_webmaster_phone',
            'imprint_section_bildrechte' => 'imprint_optional_image_rights',
            'imprint_section_bildrechte_text' => 'imprint_optional_image_rights_content',
            'imprint_section_extra' => 'imprint_optional_new_section',
            'imprint_section_extra_text' => 'imprint_optional_new_section_content',
            'display_template_newsletter' => 'privacy_services_newsletter',
            'display_template_contactform' => 'privacy_services_contact_form',
            'display_template_registrationform' => 'privacy_services_registration_forms',
            'display_template_youtube' => 'privacy_external_services_youtube',
            'display_template_slideshare' => 'privacy_external_services_slideshare',
            'display_template_vimeo' => 'privacy_external_services_vimeo',
            'display_template_vgwort' => 'privacy_external_services_vgword',
            'display_template_siteimprove' => 'privacy_external_services_siteimprove',
            'display_template_varifast' => 'privacy_external_services_varifast',
            'privacy_section_extra' => 'privacy_optional_new_section',
            'privacy_section_extra_text' => 'privacy_optional_new_section_content',
            'privacy_section_owndsb' => 'privacy_optional_dpo',
            'privacy_section_owndsb_text' => 'privacy_optional_dpo_content',
            'accessibility_methodology' => 'accessibility_compliance_status_method',
            'accessibility_creation_date' => 'accessibility_compliance_status_creation_date',
            'accessibility_last_review_date' => 'accessibility_compliance_status_last_review_date',
            'accessibility_testurl' => 'accessibility_compliance_status_report_url',
            'accessibility_non_accessible_content_helper' => 'accessibility_statement_non_accessible_content_helper',
            'accessibility_non_accessible_content_faillist' => 'accessibility_statement_non_accessible_content_list',
            'accessibility_non_accessible_content' => 'accessibility_statement_non_accessible_content_text',
            'accessibility_non_accessible_content_reasons' => 'accessibility_statement_non_accessible_content_reason',
            'accessibility_non_accessible_content_alternatives' => 'accessibility_statement_non_accessible_content_alternative',
            'accessibility_feedback_contactname' => 'accessibility_feedback_contact_person',
            'accessibility_feedback_cc' => 'accessibility_feedback_email_cc',
            'accessibility_feedback_phone' => 'accessibility_feedback_contact_phone',
            'accessibility_feedback_address' => 'accessibility_feedback_contact_address',
        ];
    }

    /**
     * Options map (v2.0.0).
     * @return array
     */
    protected static function optionsMap200()
    {
        return [
            //'privacy_external_services_vgword' => 'vgword',
            //'privacy_external_services_varifast' => 'varifast',
            'privacy_external_services_youtube' => 'youtube',
            'privacy_external_services_vimeo' => 'vimeo',
            'privacy_external_services_slideshare' => 'slideshare',
            'privacy_external_services_siteimprove' => 'siteimprove_analytics',
        ];
    }
}
