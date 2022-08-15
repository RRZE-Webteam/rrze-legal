<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

use RRZE\Legal\IP\{IP, RemoteAddress};

/**
 * Class Cache
 * @package RRZE\Legal
 */
class Cache
{
    public static function skipOnIp()
    {
        // RRZE Cache Plugin
        if (self::checkIpAddressRange(self::rrzeCacheSkipOnIp())) {
            return true;
        }

        return false;
    }

    /**
     * RRZE Cache Plugin
     * Read the skip on ip file.
     * @return array The ip addresses array.
     */
    public static function rrzeCacheSkipOnIp()
    {
        $ipFile = WP_CONTENT_DIR .
            DIRECTORY_SEPARATOR .
            'plugins' .
            DIRECTORY_SEPARATOR .
            'rrze-cache' .
            DIRECTORY_SEPARATOR .
            'skip-on-ip.network';

        // Check if the ip addresses file is readable.
        if (is_readable($ipFile) && ($fileContent = file_get_contents($ipFile)) !== false && (filesize($ipFile) > 0)) {
            $ipAry = json_decode($fileContent, true);
        }
        return !empty($ipAry) ? $ipAry : [];
    }

    public static function checkIpAddressRange($ipAddresses = [])
    {
        if (empty($ipAddresses) || !is_array($ipAddresses)) {
            return false;
        }

        $remoteAddress = self::getRemoteIpAddress();
        if (!$remoteAddress) {
            return false;
        }

        $ip = IP::fromStringIP($remoteAddress);
        if ($ip->isInRanges($ipAddresses)) {
            return true;
        }

        return false;
    }

    public static function getRemoteIpAddress()
    {
        $remoteAddress = new RemoteAddress();
        return $remoteAddress->getIpAddress();
    }

    /**
     * Flush Cache
     * @return void
     */
    public static function flush()
    {
        // RRZE Cache Plugin
        if (method_exists('\RRZE\Cache\Flush', 'flushCache')) {
            \RRZE\Cache\Flush::flushCache();
        }
    }
}
