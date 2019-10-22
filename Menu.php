<?php

namespace Piwik\Plugins\WAIPortal;

use Piwik\Menu\MenuTop;
use Piwik\Piwik;
use Piwik\Plugin\Menu as MatomoMenu;

class Menu extends MatomoMenu
{
    public function configureTopMenu(MenuTop $menu)
    {
        // Intercept menu configuration to remove unwanted icons
        $menu->remove('Tag Manager');
        $menu->remove('General_Help');
        $menu->remove('General_Logout');
        if (!Piwik::hasUserSuperUserAccess()) {
            $menu->remove('CoreAdminHome_Administration');
        }
    }
}
