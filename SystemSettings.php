<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\WAIPortal;

use Piwik\Piwik;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Plugin\SystemSettings as BaseSystemSettings;
use Piwik\Validators\NotEmpty;

/**
 * System Settings for WAIPortal Theme.
 */
class SystemSettings extends BaseSystemSettings
{
    /**
     * WAI Portal URL setting.
     *
     * @var string the URL
     */
    public $waiUrl;

    /**
     * Define system settings.
     */
    protected function init()
    {
        $this->waiUrl = $this->createDescriptionSetting();
    }

    /**
     * Create WAI Portal URL setting.
     *
     * @return \Piwik\Settings\Plugin\SystemSetting the configured setting
     */
    private function createDescriptionSetting()
    {
        $default = 'https://localhost';

        return $this->makeSetting('waiUrl', $default, FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = Piwik::translate('WAIPortal_WAIPortalBaseURLTitle');
            $field->uiControl = FieldConfig::UI_CONTROL_URL;
            $field->description = Piwik::translate('WAIPortal_WAIPortalBaseURLDescription');
            $field->validators[] = new NotEmpty();
        });
    }
}
