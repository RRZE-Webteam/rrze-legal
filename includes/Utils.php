<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

class Utils
{
    /**
     * Get website's hostname.
     * @return string
     */
    public static function getSiteUrlHost()
    {
        return parse_url(get_site_url(), PHP_URL_HOST);
    }

    /**
     * Get website's domain.
     * @return string
     */
    public static function getSiteUrlDomain()
    {
        return implode('.', array_slice(explode('.', self::getSiteUrlHost()), -2, 2));
    }

    /**
     * Get website's url path.
     * @return string
     */
    public static function getSiteUrlPath()
    {
        $path = parse_url(get_site_url(), PHP_URL_PATH);
        return $path ?: '/';
    }

    /**
     * Get FAU domains.
     * @return array
     */
    public static function getFAUDomains(): array
    {
        return ['fau.de', 'fau.eu', 'uni-erlangen.de'];
    }

    /**
     * Check if the plugin is active.
     * @return boolean
     */
    public static function isPluginActive(string $plugin): bool
    {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        return is_plugin_active($plugin);
    }

    /**
     * Check if the plugin is active for network.
     * @return boolean
     */
    public static function isPluginActiveForNetwork(string $plugin): bool
    {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        return is_plugin_active_for_network($plugin);
    }

    /**
     * Redirect to the referer.
     */
    public static function redirectToReferer()
    {
        if (wp_get_referer()) {
            wp_safe_redirect(wp_get_referer());
        } else {
            wp_safe_redirect(site_url());
        }
        exit;
    }

    /**
     * Sort multiple or multi-dimensional arrays by the given key(s).
     * @return array
     */
    public static function arrayOrderby()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = [];
                foreach ($data as $key => $row) {
                    $tmp[$key] = $row[$field];
                }
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }

    /**
     * Convert hex color to hsl (hue, saturation, lightness).
     * @param mixed $hex
     * @return array
     */
    public static function hexToHsl($hex)
    {
        $rgb = self::hexToRgb($hex);
        $max = max($rgb);
        $min = min($rgb);
        $l = ($max + $min) / 2;

        if ($max == $min) {
            $h = $s = 0;
        } else {
            $diff = $max - $min;
            $s = $l > 0.5 ? $diff / (2 - $max - $min) : $diff / ($max + $min);

            switch ($max) {
                case $rgb['r']:
                    $h = ($rgb['g'] - $rgb['b']) / $diff + ($rgb['g'] < $rgb['b'] ? 6 : 0);
                    break;
                case $rgb['g']:
                    $h = ($rgb['b'] - $rgb['r']) / $diff + 2;
                    break;
                case $rgb['b']:
                    $h = ($rgb['r'] - $rgb['g']) / $diff + 4;
                    break;
            }
            $h = round($h * 60);
        }
        return [$h, $s * 100, $l * 100];
    }

    /**
     * Convert hex color to rgb.
     * @param mixed $hex
     * @return array
     */
    public static function hexToRgb($hex)
    {
        $hex = str_replace('#', '', $hex);

        if (strlen($hex) == 3) {
            $hex .= $hex;
        }

        $hex = [
            $hex[0] . $hex[1],
            $hex[2] . $hex[3],
            $hex[4] . $hex[5],
        ];

        $rgb = array_map(
            function ($part) {
                return hexdec($part) / 255;
            },
            $hex
        );

        return [
            'r' => $rgb[0],
            'g' => $rgb[1],
            'b' => $rgb[2],
        ];
    }

    /**
     * Calculates color contrast using the YIQ color space.
     * @link https://24ways.org/2010/calculating-color-contrast/
     * @param string $hexacolor
     * @return string
     */
    public static function getContrastYIQ(string $hexcolor): string
    {
        $hexcolor = preg_replace('/[^a-f0-9]/i', '', $hexcolor);
        $r = hexdec(substr($hexcolor, 0, 2));
        $g = hexdec(substr($hexcolor, 2, 2));
        $b = hexdec(substr($hexcolor, 4, 2));
        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
        return ($yiq >= 128) ? 'black' : 'white';
    }

    /**
     * Log errors by writing to the debug.log file.
     */
    public static function debug($input, string $level = 'i')
    {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        if (in_array(strtolower((string) WP_DEBUG_LOG), ['true', '1'], true)) {
            $logPath = WP_CONTENT_DIR . '/debug.log';
        } elseif (is_string(WP_DEBUG_LOG)) {
            $logPath = WP_DEBUG_LOG;
        } else {
            return;
        }
        if (is_array($input) || is_object($input)) {
            $input = print_r($input, true);
        }
        switch (strtolower($level)) {
            case 'e':
            case 'error':
                $level = 'Error';
                break;
            case 'i':
            case 'info':
                $level = 'Info';
                break;
            case 'd':
            case 'debug':
                $level = 'Debug';
                break;
            default:
                $level = 'Info';
        }
        error_log(
            date("[d-M-Y H:i:s \U\T\C]")
                . " WP $level: "
                . basename(__FILE__) . ' '
                . $input
                . PHP_EOL,
            3,
            $logPath
        );
    }
}
