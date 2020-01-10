<?php

namespace Piwik\Plugins\WAIMatomoTheme;

use Piwik\Plugin\Controller as BaseController;
use Piwik\Url;

/**
 * WAI Matomo Theme controller.
 */
class Controller extends BaseController
{
    /**
     * Logout non super-admin users.
     *
     * @throws \Exception
     */
    public function logout()
    {
        \Piwik\Plugins\Login\Controller::clearSession();
        $settings = new SystemSettings();
        $logoutUrl = $settings->waiUrl->getValue();
        Url::redirectToUrl($logoutUrl . '/websites');
    }
}
