<?php

namespace Core\Controllers;

class AppMenu
{
    const APP_MENU_THEME = AINAD_BASE_DIR.'/rofi/widgets/app-menu/app-menu.rasi';
    
    public function __construct(){}
    
    /**
     * Executes the ROFI command to open the app menu.
     *
     * @return void
     */
    public function openMenu(): void
    {
        exec('rofi -show drun -modi drun -theme '.self::APP_MENU_THEME.'> /dev/null &');
    }
}
