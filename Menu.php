<?php

namespace Piwik\Plugins\WAIMatomoTheme;

use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuTop;
use Piwik\Piwik;
use Piwik\Plugin\Menu as MatomoMenu;

/**
 * WAI Portal menu provider.
 */
class Menu extends MatomoMenu
{
    /**
     * Configure analytics items menu.
     *
     * @param MenuTop $menu the menu reference
     */
    public function configureTopMenu(MenuTop $menu)
    {
        // Intercept menu configuration to remove unwanted icons
        $menu->remove('Tag Manager');
        $menu->remove('General_Help');
        $menu->remove('General_Logout');
        if (!Piwik::isUserHasSomeWriteAccess()) {
            $menu->remove('CoreAdminHome_Administration');
        }
    }

    /**
     * Configure analytics items menu.
     *
     * @param MenuTop $menu the menu reference
     */
    public function configureAdminMenu(MenuAdmin $menuAdmin)
    {
        // Intercept menu configuration to remove unwanted items
        if (!Piwik::hasUserSuperUserAccess()) {
            $menuAdmin->remove('UsersManager_MenuPersonal', 'General_Settings');
            $menuAdmin->remove('UsersManager_MenuPersonal', 'General_Security');
            $menuAdmin->remove('CorePluginsAdmin_MenuPlatform');
            $menuAdmin->remove('CoreAdminHome_MenuMeasurables', 'CoreAdminHome_TrackingCode');
        }
    }
}
