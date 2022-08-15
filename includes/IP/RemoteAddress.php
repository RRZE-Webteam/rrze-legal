<?php

namespace RRZE\Legal\IP;

defined('ABSPATH') || exit;

class RemoteAddress
{
    public function getIpAddress()
    {
        $ipStr = $this->getIpAddressFromProxy();
        if ($ipStr) {
            return $ipStr;
        }

        // Remote IP address
        if (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }

        return '';
    }

    protected function getIpAddressFromProxy()
    {
        if (empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return false;
        }

        $ipsAry = array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));

        if (empty($this->getProxies($ipsAry))) {
            return false;
        }

        // The right-most IP address is always the IP address that connects to
        // the last proxy, which means it is the most reliable source of information.
        // @see https://en.wikipedia.org/wiki/X-Forwarded-For
        $ipStr = array_pop($ipsAry);
        return $ipStr;
    }

    protected function getProxies($ipsAry = [])
    {
        $proxies = [];

        foreach ($ipsAry as $ipStr) {
            $ip = IP::fromStringIP($ipStr);
            $host = $ip->getHostname();
            if ($host === null) {
                continue;
            }
            $proxies[] = $ipStr;
        }

        return $proxies;
    }
}
