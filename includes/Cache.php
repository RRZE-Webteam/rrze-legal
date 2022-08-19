<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

/**
 * Class Cache
 * @package RRZE\Legal
 */
class Cache
{
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
