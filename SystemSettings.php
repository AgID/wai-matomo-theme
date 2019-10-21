<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\WAIPortal;

use Piwik\Piwik;
use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;
use Piwik\Validators\NotEmpty;

/**
 * Defines Settings for WAIPortal.
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var Setting */
    public $waiUrl;

    protected function init()
    {
        $this->waiUrl = $this->createDescriptionSetting();
    }

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
