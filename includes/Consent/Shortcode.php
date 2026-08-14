<?php

namespace RRZE\Legal\Consent;

use function RRZE\Legal\consentCookies;

defined('ABSPATH') || exit;

class Shortcode
{
    /**
     * handleShortcode function.
     *
     * @param mixed $atts
     * @param mixed $content
     */
    public static function handleShortcode($atts, $content = '')
    {
        if (!empty($atts['type'])) {
            if ($atts['type'] === 'switch-consent') {
                $content = self::handleSwitchConsent($atts, $content);
            }
        }

        if (function_exists('is_feed') && is_feed()) {
            $content = '';
        }

        return $content;
    }

    /**
     * Handle switch consent.
     * @param mixed $atts
     * @param mixed $content
     */
    public static function handleSwitchConsent($atts, $content)
    {
        if (!empty($atts['id'])) {
            $cookieData = consentCookies()->getOptions();
            $cookieId = $atts['id'];
            $cookieId = $cookieData[$cookieId]['id'] ?? '';

            if ($cookieId) {
                $category = $cookieData[$cookieId]['category'];
                $label = !empty($atts['label']) ? wp_strip_all_tags((string) $atts['label']) : $cookieData[$cookieId]['name'];
                $inputId = 'rrzelegal-consent-switch-' . sanitize_html_class($cookieId);
                $descriptionId = $inputId . '-description';
                $switchText = __('Yes, I agree.', 'rrze-legal');

                $content = '<div class="RRZELegal _rrzelegal-switch-consent">';
                $content .= '<div class="_rrzelegal-content-blocker"><div class="_rrzelegal-caption">';
                $content .= '<span id="' . esc_attr($descriptionId) . '" class="sr-only">'
                    . esc_html($label) . '</span>';
                $content .= '<label for="' . esc_attr($inputId) . '" class="_rrzelegal-btn-switch _rrzelegal-btn-switch--textRight">';
                $content .= '<input type="checkbox" id="' . esc_attr($inputId)
                    . '" data-cookie-group="' . esc_attr($category) . '" name="rrzelegalCookie[' . esc_attr(
                        $category
                    ) . '][]" value="' . esc_attr($cookieId) . '" aria-describedby="' . esc_attr($descriptionId)
                    . '" data-rrzelegal-cookie-switch />';
                $content .= '<span class="_rrzelegal-slider" aria-hidden="true"></span>';
                $content .= '<span class="_rrzelegal-btn-switch-status" data-active="'
                    . esc_attr($switchText) . '" data-inactive="'
                    . esc_attr($switchText) . '" aria-hidden="true"></span>';
                $content .= '<span class="sr-only">' . esc_html($switchText) . '</span>';
                $content .= '</label>';
                $content .= '</div></div>';
                $content .= '</div>';
            }
        }
        return $content;
    }
}
