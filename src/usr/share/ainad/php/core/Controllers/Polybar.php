<?php

namespace Core\Controllers;

use \Core\Classes\FileManager;
use \Core\Classes\IniParser;
use \Core\Interfaces\CommonFiles;
use \Core\Interfaces\CommonDirectories;

/**
 * This class refers to the Polybar panel itself, not the modules.
 */
class Polybar implements CommonFiles, CommonDirectories
{
    use \Core\Traits\CommonMethods;

    const POLYBAR_MAIN_CONFIG = AINAD_BASE_DIR.'/polybar/bar-main.ini';

    public function __construct(){}
    
    /**
     * Gets the Polybar PIDs and store it in a file to be used later when
     * needed.
     * 
     * It also store if it is visible or not
     *
     * @return void
     */
    public function storePolybarPids(): void
    {
        exec("pgrep polybar", $pids);
        FileManager::writePhpVar(self::POLYBAR_DATA, ['bg' => $pids[0], 'main' => $pids[1], 'visible' => true]);
    }
    
    /**
     * Defines the locale of the Polybar on boot, based on the system locale.
     * This is important to show date and other information properly.
     *
     * @return void
     */
    public function setLocale(): void
    {
        $system = require(self::SYSTEM_DATA);

        $iniData = new IniParser(self::POLYBAR_MAIN_CONFIG);
        $iniData->setData('bar/main', 'locale', $system['locale']);
        $iniData->writeFile();
    }

    /**
     * Detects if the current window is in fullscreen and hides it if it is.
     *
     * Polybar has a problem with its tray: it remains on top of the fullscreen
     * windows and the only way to minimize the problem is to hide the entire
     * panel.
     *
     * @return void
     */
    public function hideOnFullscreen()
    {
        $this->setActiveWindow();
        $this->setPolybarPids();

        /**
         * Extract the state of the current window.
         */
        $windowState = explode(' = ', exec('xprop -id '.$this->activeWindow.' _NET_WM_STATE'))[1] ?? null;
        
        /**
         * If the state is equal to _NET_WM_STATE_FULLSCREEN, it means that the
         * window is in fullscreen mode, so, the method will hides all Polybars
         * (bg and main).
         *
         * To save CPU and HD/SDD processing, the commands will be executed
         * once, only when the `visible` is set to true. It won't be executed
         * again until the `visible` is set to true again.
         */
        if ($windowState == '_NET_WM_STATE_FULLSCREEN') {
            if ($this->polybarData['visible']) {
                exec('polybar-msg cmd hide');

                $this->polybarData['visible'] = false;
                
                FileManager::writePhpVar(self::POLYBAR_DATA, $this->polybarData);
            }

        /**
         * However, if the state is not _NET_WM_STATE_FULLSCREEN, then it means
         * that the window is not in fullscreen. So it shows the panel back.
         * 
         * Also to save CPU and HD/SDD processing, the commands will be executed
         * once, only when the `visible` is set to false. It won't be executed
         * again until the `visible` is set to false again.
         */
        } else {
            if (!$this->polybarData['visible']) {
                exec('
                    polybar-msg -p '.$this->polybarData['bg'].' cmd show;
                    polybar-msg -p '.$this->polybarData['main'].' cmd show;
                ');

                $this->polybarData['visible'] = true;

                FileManager::writePhpVar(self::POLYBAR_DATA, $this->polybarData);
            }
        }
    }
}
