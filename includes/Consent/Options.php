<?php

namespace RRZE\Legal\Consent;

defined('ABSPATH') || exit;

use RRZE\Legal\{Settings, Template, Locale, Utils};
use function RRZE\Legal\{plugin, network};

class Options extends Settings
{
    private $isPluginActiveForNetwork;

    public function __construct()
    {
        parent::__construct();
        $this->optionName = 'rrze_legal_consent';
        $this->settingsFilename = 'consent';

        $this->isPluginActiveForNetwork = Utils::isPluginActiveForNetwork(plugin()->getBaseName());
    }

    protected function postSanitizeOptions($input, $hasError)
    {
        if (!$hasError && $this->options['banner_update_version']) {
            $this->updateCookieVersion();
            $this->options['banner_update_version'] = '0';
        }
        return $this->options;
    }

    public function getSiteUrlHost()
    {
        return Utils::getSiteUrlHost();
    }

    public function getSiteUrlPath()
    {
        return Utils::getSiteUrlPath();
    }

    public function bannerDefaultDescription()
    {
        $langCode = is_user_logged_in() && is_admin() ? Locale::getUserLangCode() : Locale::getLangCode();
        $tpl = plugin()->getPath(Template::CONSENT_PATH) . 'banner-default-description' . '-' . $langCode . '.html';
        return is_readable($tpl) ? $this->getTplContent($tpl) : '';
    }

    protected function getTplContent($template, $options = [])
    {
        $content = Template::getContent($template, $options);
        $content = preg_replace('/(^|[^\n\r])[\r\n](?![\n\r])/', '$1 ', $content);
        return $content;
    }

    public function hasNetworkPriority()
    {
        return $this->isPluginActiveForNetwork && !network()->hasException() ? true : false;
    }

    public function getCookieVersion()
    {
        if ($this->hasNetworkPriority()) {
            return (int) get_site_option('rrze_legal_consent_cookie_version', 1);
        } else {
            return (int) get_option('rrze_legal_consent_cookie_version', 1);
        }
    }

    public function updateCookieVersion()
    {
        $currentVersion = $this->getCookieVersion();
        update_option('rrze_legal_consent_cookie_version', $currentVersion + 1);
    }

    public function isBannerActive()
    {
        if ($this->hasNetworkPriority()) {
            return (bool) network()->getOption('network_banner', 'status');
        } else {
            return (bool) $this->getOption('banner', 'status');
        }
    }

    public function isTestModeActive()
    {
        return (bool) $this->getOption('banner', 'test_mode');
    }

    public function isCookieForBotsActive()
    {
        if ($this->hasNetworkPriority()) {
            return (bool) network()->getOption('network_banner', 'cookies_for_bots');
        } else {
            return (bool) $this->getOption('banner', 'cookies_for_bots');
        }
    }

    public function isRespectDoNotTrackActive()
    {
        if ($this->hasNetworkPriority()) {
            return (bool) network()->getOption('network_banner', 'respect_do_not_track');
        } else {
            return (bool) $this->getOption('banner', 'respect_do_not_track');
        }
    }

    public function isReloadAfterOptoutActive()
    {
        if ($this->hasNetworkPriority()) {
            return (bool) network()->getOption('network_banner', 'reload_after_optout');
        } else {
            return (bool) $this->getOption('banner', 'reload_after_optout');
        }
    }

    public function isIgnorePreselectedStatusActive()
    {
        if ($this->hasNetworkPriority()) {
            return (bool) network()->getOption('network_banner', 'ignore_preselected_status');
        } else {
            return (bool) $this->getOption('banner', 'ignore_preselected_status');
        }
    }
}
