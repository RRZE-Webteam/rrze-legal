<?php

namespace RRZE\Legal\Consent;

defined('ABSPATH') || exit;

use RRZE\Legal\HtmlMinifier;
use function RRZE\Legal\{plugin, tos, consent, consentCookies};

class Banner
{
    public static function add()
    {
        // Privacy policy link
        $privacyPolicyUrl = tos()->endpointUrl('privacy');
        $privacyPolicyLinkText = __('Privacy policy', 'rrze-legal');
        $privacyPolicyLink = sprintf('<a href="%1$s" tabindex="0">%2$s</a>', $privacyPolicyUrl, $privacyPolicyLinkText);

        // Imprint link
        $imprintUrl = tos()->endpointUrl('imprint');
        $imprintLinkText = tos()->endpointTitle('imprint');

        // Accessibility link
        $accessibilityUrl = tos()->endpointUrl('accessibility');
        $accessibilityLinkText = tos()->endpointTitle('accessibility');

        // Show accept all button
        $bannerShowAcceptAllButton = consent()->getOption('banner', 'show_accept_all_button');

        // Main window
        $bannerTextHeadline = consent()->getOption('banner', 'headline');
        $bannerTextDescription = consent()->getOption('banner', 'description_text');
        $bannerTextDescription = str_replace(
            '{{privacy_policy_link}}',
            $privacyPolicyLink,
            $bannerTextDescription
        );
        $bannerTextManageLink = __('Individual privacy settings', 'rrze-legal');
        $bannerTextRefuseLink = consent()->getOption('banner', 'refuse_btn_txt');

        // Preference window
        $bannerPreferenceTextHeadline = __('Privacy Settings', 'rrze-legal');
        $bannerPreferenceTextDescription = '<span class="_rrzelegal-paragraph _rrzelegal-text-description">' .
            consent()->getOption('banner', 'preference_text') .
            '</span>';
        $bannerPreferenceTextDescription = str_replace(
            '{{privacy_policy_link}}',
            $privacyPolicyLink,
            $bannerPreferenceTextDescription
        );
        $bannerPreferenceTextSaveButton = consent()->getOption('banner', 'save_btn_txt');
        $bannerPreferenceTextAcceptAllButton = consent()->getOption('banner', 'accept_all_btn_txt');
        $bannerPreferenceTextRefuseLink = consent()->getOption('banner', 'refuse_btn_txt');
        $bannerPreferenceTextBackLink = __('Back', 'rrze-legal');
        $bannerPreferenceTextSwitchStatusActive = __('On', 'rrze-legal');
        $bannerPreferenceTextSwitchStatusInactive = __('Off', 'rrze-legal');
        $bannerPreferenceTextShowCookieLink = __('Show Cookie Information', 'rrze-legal');
        $bannerPreferenceTextHideCookieLink = __('Hide Cookie Information', 'rrze-legal');

        // Cookie details (table)
        $bannerCookieDetailsTableAccept = __('Accept', 'rrze-legal');
        $bannerCookieDetailsTableName = __('Name', 'rrze-legal');
        $bannerCookieDetailsTableProvider = __('Provider', 'rrze-legal');
        $bannerCookieDetailsTablePurpose = __('Purpose', 'rrze-legal');
        $bannerCookieDetailsTablePrivacyPolicy = __('Privacy Policy', 'rrze-legal');
        $bannerCookieDetailsTableHosts = __('Hosts', 'rrze-legal');
        $bannerCookieDetailsTableCookieName = __('Cookie Name', 'rrze-legal');
        $bannerCookieDetailsTableCookieExpiry = __('Cookie Expiry', 'rrze-legal');

        // Cookie categories
        $categories = consentCookies()->getAllCookieCategories();
        if (!empty($categories)) {
            foreach ($categories as $key => $category) {
                $categories[$key]['has_cookies'] = !empty($category['cookies']);
                $categories[$key]['description'] = nl2br($category['description']);
            }
        }

        // Disable indexing of the consent banner data
        echo '<!--googleoff: all-->', PHP_EOL;
        echo '<div data-nosnippet>';

        echo '<script id="RRZELegalBannerWrap" type="text/template">';

        // Include banner templates
        $minifier = new HtmlMinifier();
        $bannerTemplateFile = plugin()->getPath('templates/consent') . 'banner-layout.php';
        $cookiePreferenceTemplateFile = plugin()->getPath('templates/consent') . 'banner-preferences.php';
        ob_start();
        include $bannerTemplateFile;
        $content = ob_get_clean();
        echo $minifier->minify($content);
        echo '</script>';

        // Re-enable indexing
        echo '</div>', PHP_EOL;
        echo '<!--googleon: all-->', PHP_EOL;
    }
}
