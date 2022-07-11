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
     * Flush RRZE\Cache
     * @return void
     */
    public static function flush()
    {
        if (method_exists('\RRZE\Cache\Flush', 'flushCache')) {
            \RRZE\Cache\Flush::flushCache();
        }
    }
}
