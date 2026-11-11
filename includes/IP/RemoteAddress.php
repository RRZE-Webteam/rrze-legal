<?php

namespace RRZE\Legal\IP;

defined('ABSPATH') || exit;

class RemoteAddress
{
    protected array $trustedProxies;

    /**
     * @param array|null $trustedProxies Trusted proxy IP addresses or CIDRs.
     */
    public function __construct(?array $trustedProxies = null)
    {
        $trustedProxies = $trustedProxies ?? apply_filters('rrze_trusted_proxies', []);
        $this->trustedProxies = is_array($trustedProxies) ? $trustedProxies : [];
    }

    public function getIpAddress()
    {
        $remoteAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!$this->isValidIp($remoteAddress)) {
            return '';
        }

        if (!$this->isTrustedProxy($remoteAddress)) {
            return $remoteAddress;
        }

        $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        if (!is_string($forwarded) || trim($forwarded) === '') {
            return '';
        }

        foreach (array_reverse(explode(',', $forwarded)) as $address) {
            $address = trim($address);
            if (!$this->isValidIp($address)) {
                return '';
            }
            if (!$this->isTrustedProxy($address)) {
                return $address;
            }
        }

        return '';
    }

    protected function isValidIp($ip): bool
    {
        return is_string($ip) && filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    protected function isTrustedProxy(string $ip): bool
    {
        foreach ($this->trustedProxies as $range) {
            if ($this->isIpInRange($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    protected function isIpInRange(string $ip, $range): bool
    {
        if (!$this->isValidIp($ip) || !is_string($range)) {
            return false;
        }

        $parts = explode('/', trim($range));
        $subnet = $parts[0];
        if (count($parts) > 2 || !$this->isValidIp($subnet)) {
            return false;
        }

        $maxBits = str_contains($subnet, ':') ? 128 : 32;
        $bits = $parts[1] ?? (string) $maxBits;
        if (!ctype_digit($bits) || (int) $bits > $maxBits) {
            return false;
        }

        return IP::fromStringIP($ip)->isInRange($subnet . '/' . (int) $bits);
    }
}
