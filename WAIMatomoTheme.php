<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\WAIMatomoTheme;

use Piwik\Filesystem;
use Piwik\Option;
use Piwik\Plugin;
use Piwik\Plugins\CoreAdminHome\CustomLogo;
use Piwik\View;

/**
 * WAI Portal theme plugin.
 */
class WAIMatomoTheme extends Plugin
{
    /**
     * The base plugin path.
     *
     * @var string the path
     */
    const PLUGIN_PATH = 'plugins/WAIMatomoTheme';

    /**
     * File backup suffix
     *
     * @var string the suffix
     */
    const BACKUP_FILE_SUFFIX = '.bkp';

    /**
     * Matomo manifest path
     *
     * @var string the path
     */
    const MANIFEST_PATH = 'config/manifest.inc.php';

    /**
     * Matomo TPL templates to override paths
     *
     * @var array the paths list
     */
    const TPL_TEMPLATES_PATHS = [
        'maintenance' => 'plugins/Morpheus/templates/maintenance.tpl',
        'error-header' => 'plugins/Morpheus/templates/simpleLayoutHeader.tpl',
        'error-footer' => 'plugins/Morpheus/templates/simpleLayoutFooter.tpl'
    ];

    /**
     * Socials list
     *
     * @var mixed the list
     */
    private $socials;

    /**
     * Footer list
     *
     * @var mixed the list
     */
    private $footerLinks;

    /**
     * WAIMatomoTheme constructor.
     * @param string|bool $pluginName A plugin name to force. If not supplied, it is set
     *                                to the last part of the class name.
     * @throws \Exception If plugin metadata is defined in both the getInformation() method
     *                    and the **plugin.json** file.
     */
    public function __construct($pluginName = false)
    {
        parent::__construct($pluginName);
        $this->socials = json_decode(@file_get_contents(self::PLUGIN_PATH . '/data/socials.json'), true);
        $this->footerLinks = json_decode(@file_get_contents(self::PLUGIN_PATH . '/data/footer_links.json'), true);
    }

    /**
     * Register observer to events.
     *
     * @return array the list of events with associated observer
     */
    public function registerEvents() {
        return array(
            'AssetManager.getStylesheetFiles' => array(
                'function' => 'addCssFiles',
                'after' => true,
            ),
            'AssetManager.getJavaScriptFiles' => array(
                'function' => 'addJavaScriptFiles',
                'after' => true,
            ),
            'Template.bodyClass' => 'handleBodyClass',
            'Template.beforeTopBar' => 'handleAddTopBar',
            'Template.header' => 'handleTemplateHeader',
            'Template.pageFooter' => 'handleTemplatePageFooter',
            'Template.loginNav' => 'handleLoginNav',
            'SystemSettings.updated' => 'handleSystemSettingsUpdate',
            'Widget.filterWidgets' => 'filterWidgets',
        );
    }

    public function addCssFiles(&$stylesheets) {
        $stylesheets[] = 'plugins/WAIMatomoTheme/stylesheets/wai-matomo-theme.min.css';
    }

    public function addJavaScriptFiles(&$jsFiles) {
        $jsFiles[] = 'plugins/WAIMatomoTheme/javascripts/wai-matomo-theme.min.js';
    }

    public function handleBodyClass(&$outstring, $page) {
        return $outstring .= ' wai-theme';
    }

    /**
     * Handle "before adding top bar" event.
     *
     * @param string $outstring the HTML string to modify
     * @param string $page the current location
     * @param string|null $userLogin the current user login
     * @param string|null $topMenu the top menu
     */
    public function handleAddTopBar(&$outstring, $page, $userLogin = null, $topMenu = null)
    {
        if ('login' === $page) {
            $outstring .= $this->handleTemplateHeader($outstring);
        } else {
            $outstring .= (new View('@WAIMatomoTheme/itSiteName'))->render();
        }
    }

    /**
     * Handle "add header" event.
     *
     * @param string $outstring the HTML string to modify
     */
    public function handleTemplateHeader(&$outstring)
    {
        $view = new View('@WAIMatomoTheme/itHeader');
        $view->pluginPath = self::PLUGIN_PATH;
        $settings = new SystemSettings();
        $view->waiUrl = $settings->waiUrl->getValue();
        $outstring .= $view->render();
    }

    /**
     * Handle "add page page footer" event.
     *
     * @param string $outstring the HTML string to modify
     */
    public function handleTemplatePageFooter(&$outstring)
    {
        $view = new View('@WAIMatomoTheme/itFooter');
        $view->socials = $this->socials['links'];
        $view->owner = $this->footerLinks['owner'];
        $view->links = $this->footerLinks['links'];
        $settings = new SystemSettings();
        $view->waiUrl = $settings->waiUrl->getValue();
        $view->pluginPath = self::PLUGIN_PATH;
        $outstring .= $view->render();
    }

    /**
     * Handle "add navigation bar to login page" event.
     *
     * @param string $outstring the HTML string to modify
     * @param string $position "top" to signal initial inserting before default content,
     *                         "bottom" ti signal inserting after default content
     */
    public function handleLoginNav(&$outstring, $position)
    {
        if ('bottom' === $position) {
            $outstring .= '</div>';

            return;
        }

        $outstring .= '<div class="hide">';
    }

    /**
     * Handle system settings update event.
     *
     * @param SystemSetting $settings the settings reference
     */
    public function handleSystemSettingsUpdate($settings) {
        if ($settings->getPluginName() === $this->pluginName) {
            $value = $settings->waiUrl->getValue();
            $this->updateWaiUrlReferences($value);
        }
    }

    /**
     * Remove some widgets.
     *
     * @param WidgetsList $list the list of all widgets
     */
    public function filterWidgets($list) {
        $list->remove('About Matomo', 'CoreHome_SupportPiwik');
        $list->remove('About Matomo', 'Installation_Welcome');
    }

    /**
     * Manage plugin loaded event.
     */
    public function postLoad()
    {
        if (!Option::get('branding_use_custom_logo')) {
            $this->activate();
        }
    }

    /**
     * Manage plugin activation.
     */
    public function activate() {
        @copy(PIWIK_DOCUMENT_ROOT . '/plugins/WAIMatomoTheme/images/wai-logo.png', PIWIK_DOCUMENT_ROOT . '/' . CustomLogo::getPathUserLogo());
        @copy(PIWIK_DOCUMENT_ROOT . '/plugins/WAIMatomoTheme/svg/wai-logo.svg', PIWIK_DOCUMENT_ROOT . '/' . CustomLogo::getPathUserSvgLogo());
        @copy(PIWIK_DOCUMENT_ROOT . '/plugins/WAIMatomoTheme/icons/favicon-32x32.png', PIWIK_DOCUMENT_ROOT . '/' . CustomLogo::getPathUserFavicon());
        Option::set('branding_use_custom_logo', '1', true);

        $manifest = @file_get_contents(PIWIK_DOCUMENT_ROOT . '/' . self::MANIFEST_PATH);
        $this->backupAndReplaceFile('plugins/WAIMatomoTheme/templates/maintenance.tpl', self::TPL_TEMPLATES_PATHS['maintenance'], $manifest);
        $this->backupAndReplaceFile('plugins/WAIMatomoTheme/templates/simpleLayoutHeader.tpl', self::TPL_TEMPLATES_PATHS['error-header'], $manifest);
        $this->backupAndReplaceFile('plugins/WAIMatomoTheme/templates/simpleLayoutFooter.tpl', self::TPL_TEMPLATES_PATHS['error-footer'], $manifest);
        $result = @file_put_contents(PIWIK_DOCUMENT_ROOT . '/' . self::MANIFEST_PATH . self::BACKUP_FILE_SUFFIX, $manifest, LOCK_EX);
        if ($result) {
            @rename(PIWIK_DOCUMENT_ROOT . '/' . self::MANIFEST_PATH . self::BACKUP_FILE_SUFFIX, PIWIK_DOCUMENT_ROOT . '/' . self::MANIFEST_PATH);
        }

        $settings = new SystemSettings();
        $waiUrl = $settings->waiUrl->getValue();
        $this->updateWaiUrlReferences($waiUrl);
    }

    /**
     * Manage plugin deactivation.
     */
    public function deactivate() {
        Filesystem::remove(PIWIK_DOCUMENT_ROOT . '/' . CustomLogo::getPathUserLogo());
        Filesystem::remove(PIWIK_DOCUMENT_ROOT . '/' .CustomLogo::getPathUserSvgLogo());
        Filesystem::remove(PIWIK_DOCUMENT_ROOT . '/' .CustomLogo::getPathUserFavicon());
        Option::set('branding_use_custom_logo', '0', true);

        $manifest = @file_get_contents(PIWIK_DOCUMENT_ROOT . '/' . self::MANIFEST_PATH);
        $this->restoreOriginalFiles(self::TPL_TEMPLATES_PATHS['maintenance'] . self::BACKUP_FILE_SUFFIX, $manifest);
        $this->restoreOriginalFiles(self::TPL_TEMPLATES_PATHS['error-header'] . self::BACKUP_FILE_SUFFIX, $manifest);
        $this->restoreOriginalFiles(self::TPL_TEMPLATES_PATHS['error-footer'] . self::BACKUP_FILE_SUFFIX, $manifest);
        $result = @file_put_contents(PIWIK_DOCUMENT_ROOT . '/' . self::MANIFEST_PATH . self::BACKUP_FILE_SUFFIX, $manifest, LOCK_EX);
        if ($result) {
            @rename(PIWIK_DOCUMENT_ROOT . '/' . self::MANIFEST_PATH . self::BACKUP_FILE_SUFFIX, PIWIK_DOCUMENT_ROOT . '/' . self::MANIFEST_PATH);
        }
    }

    /**
     * Replace a file backing up the existing destination.
     *
     * @param string $sourceFile overriding relative file path
     * @param string $destinationFile destination relative file path
     * @param string $manifest loaded matomo manifest reference
     */
    private function backupAndReplaceFile($sourceFile, $destinationFile, &$manifest) {
        @copy(PIWIK_DOCUMENT_ROOT . '/' . $destinationFile, PIWIK_DOCUMENT_ROOT . '/' . $destinationFile . self::BACKUP_FILE_SUFFIX);

        @copy(PIWIK_DOCUMENT_ROOT . '/' . $sourceFile, PIWIK_DOCUMENT_ROOT . '/' . $destinationFile);

        $this->updateManifestCheck($destinationFile, $manifest);
    }

    /**
     * Restore a backed up file.
     *
     * @param string $sourceFile relative file path to restore
     * @param string $manifest loaded matomo manifest reference
     */
    private function restoreOriginalFiles($sourceFile, &$manifest) {
        $restoredFileName = rtrim($sourceFile, self::BACKUP_FILE_SUFFIX);
        @copy($sourceFile, $restoredFileName);
        @unlink(PIWIK_DOCUMENT_ROOT . '/' . $sourceFile);

        $this->updateManifestCheck($restoredFileName, $manifest);
    }

    /**
     * Update file size and MD5 hash into matomo manifest.
     *
     * @param string $destinationFile relative file path to update
     * @param string $manifest loaded matomo manifest reference
     */
    private function updateManifestCheck($destinationFile, &$manifest) {
        if (function_exists('md5_file')) {
            $manifest = preg_replace(
                '/"' . str_replace('/', '\/', $destinationFile) . '" => array\("[0-9]+", "[a-z0-9]+"\)(,)?/',
                '"' . $destinationFile . '" => array("' . filesize(PIWIK_DOCUMENT_ROOT . '/' . $destinationFile) . '", "' . md5_file(PIWIK_DOCUMENT_ROOT . '/' . $destinationFile) . '")$1',
                $manifest
            );
        }
    }

    /**
     * Update WAI URL into static templates.
     *
     * @param string $newWaiUrlValue the new URL
     */
    private function updateWaiUrlReferences($newWaiUrlValue) {
        $template = @file_get_contents(PIWIK_DOCUMENT_ROOT . '/' . self::TPL_TEMPLATES_PATHS['error-header']);
        $template = preg_replace('/^(.*)<a class="btn btn-outline-secondary" role="button" href="(.*)">Home page<\/a>$/m', '$1<a class="btn btn-outline-secondary" role="button" href="' . $newWaiUrlValue . '">Home page</a>', $template);
        $result = @file_put_contents(PIWIK_DOCUMENT_ROOT . '/' . self::TPL_TEMPLATES_PATHS['error-header'] . self::BACKUP_FILE_SUFFIX, $template, LOCK_EX);
        if ($result) {
            @rename(PIWIK_DOCUMENT_ROOT . '/' . self::TPL_TEMPLATES_PATHS['error-header'] . self::BACKUP_FILE_SUFFIX, PIWIK_DOCUMENT_ROOT . '/' . self::TPL_TEMPLATES_PATHS['error-header']);
        }

        $manifest = @file_get_contents(PIWIK_DOCUMENT_ROOT . '/' . self::MANIFEST_PATH);
        $this->updateManifestCheck(self::TPL_TEMPLATES_PATHS['error-header'], $manifest);
        $result = @file_put_contents(PIWIK_DOCUMENT_ROOT . '/' . self::MANIFEST_PATH . self::BACKUP_FILE_SUFFIX, $manifest, LOCK_EX);
        if ($result) {
            @rename(PIWIK_DOCUMENT_ROOT . '/' . self::MANIFEST_PATH . self::BACKUP_FILE_SUFFIX, PIWIK_DOCUMENT_ROOT . '/' . self::MANIFEST_PATH);
        }
    }
}
