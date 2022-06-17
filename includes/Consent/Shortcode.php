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

                $content = '<div class="RRZELegal _rrzelegal-switch-consent">';
                $content .= '<div class="_rrzelegal-content-blocker"><div class="_rrzelegal-caption">';
                $content .= '<label class="_rrzelegal-btn-switch _rrzelegal-btn-switch--textRight">';
                $content .= '<input type="checkbox" id="rrzelegal-cookie-' . $cookieId
                    . '" data-cookie-group="' . esc_attr($category) . '" name="rrzelegalCookie[' . esc_attr(
                        $category
                    ) . '][]" value="' . esc_attr($cookieId) . '" data-rrzelegal-cookie-switch />';
                $content .= '<span class="_rrzelegal-slider"></span>';
                $content .= '<span class="_rrzelegal-btn-switch-status" data-active="'
                    . esc_html(__('Yes, I agree.', 'rrze-legal')) . '" data-inactive="'
                    . esc_html(__('Yes, I agree.', 'rrze-legal')) . '" aria-hidden="true"></span>';
                $content .= '</label>';
                $content .= '</div></div>';
                $content .= '</div>';
            }
        }
        return $content;
    }
}
