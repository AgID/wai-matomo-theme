<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\WAIPortal;

use Piwik\Filesystem;
use Piwik\Option;
use Piwik\Plugin;
use Piwik\Plugins\CoreAdminHome\CustomLogo;
use Piwik\View;

class WAIPortal extends Plugin
{
    const PLUGIN_PATH = 'plugins/WAIPortal';

    private $socials;

    private $footerLinks;

    public function __construct($pluginName = false)
    {
        parent::__construct($pluginName);
        $this->socials = json_decode(@file_get_contents(self::PLUGIN_PATH . '/data/socials.json'), true);
        $this->footerLinks = json_decode(@file_get_contents(self::PLUGIN_PATH . '/data/footer_links.json'), true);
    }

    public function registerEvents() {
        return array(
            'Template.beforeTopBar' => 'handleAddTopBar',
            'Template.header' => 'handleTemplateHeader',
            'Template.pageFooter' => 'handleTemplatePageFooter',
            'Template.loginNav' => 'handleLoginNav',
        );
    }

    public function handleAddTopBar(&$outstring, $page, $userLogin = null, $topMenu = null)
    {
        if ('login' === $page) {
            $outstring .= $this->handleTemplateHeader($outstring);
        } else {
            $outstring .= (new View('@WAIPortal/itSiteName'))->render();
        }
    }

    public function handleTemplateHeader(&$outstring)
    {
        $view = new View('@WAIPortal/itHeader');
        $view->pluginPath = self::PLUGIN_PATH;
        $settings = new SystemSettings();
        $view->waiUrl = $settings->waiUrl->getValue();
        $outstring .= $view->render();
    }

    public function handleTemplatePageFooter(&$outstring)
    {
        $view = new View('@WAIPortal/itFooter');
        $view->socials = $this->socials['links'];
        $view->links = $this->footerLinks['links'];
        $settings = new SystemSettings();
        $view->waiUrl = $settings->waiUrl->getValue();
        $view->pluginPath = self::PLUGIN_PATH;
        $outstring .= $view->render();
    }

    public function handleLoginNav(&$outstring, $position)
    {
        if ('bottom' === $position) {
            $outstring .= '</div>';

            return;
        }

        $outstring .= '<div class="hide">';
    }

    public function activate() {
        @copy(PIWIK_DOCUMENT_ROOT . '/plugins/WAIPortal/images/logo.png', PIWIK_DOCUMENT_ROOT . '/' . CustomLogo::getPathUserLogo());
        @copy(PIWIK_DOCUMENT_ROOT . '/plugins/WAIPortal/svg/logo.svg', PIWIK_DOCUMENT_ROOT . '/' . CustomLogo::getPathUserSvgLogo());
        @copy(PIWIK_DOCUMENT_ROOT . '/plugins/WAIPortal/icons/favicon-32x32.png', PIWIK_DOCUMENT_ROOT . '/' . CustomLogo::getPathUserFavicon());
        Option::set('branding_use_custom_logo', '1', true);
    }

    public function deactivate() {
        Filesystem::remove(PIWIK_DOCUMENT_ROOT . '/' . CustomLogo::getPathUserLogo());
        Filesystem::remove(PIWIK_DOCUMENT_ROOT . '/' .CustomLogo::getPathUserSvgLogo());
        Filesystem::remove(PIWIK_DOCUMENT_ROOT . '/' .CustomLogo::getPathUserFavicon());
        Option::set('branding_use_custom_logo', '0', true);
    }
}
