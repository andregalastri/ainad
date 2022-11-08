<?php

namespace Core\Controllers;

/**
 * Contains the methods and properties related to the Polybar Taskbar module.
 */
class AppMenu
{
    const APP_MENU_THEME = AINAD_BASE_DIR.'/rofi/widgets/app-menu/app-menu.rasi';
    
    /**
     * __construct
     *
     * @param  mixed $client
     * @return void
     */
    public function __construct(){}

    public function openMenu(): void
    {
        exec('rofi -show drun -modi drun -theme '.self::APP_MENU_THEME.'> /dev/null &');
    }
}
