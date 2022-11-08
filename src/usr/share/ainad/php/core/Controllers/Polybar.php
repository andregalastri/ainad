<?php

namespace Core\Controllers;

use \Core\Classes\FileManager;
use \Core\Classes\IniParser;
use \Core\Interfaces\CommonFiles;
use \Core\Interfaces\CommonDirectories;

/**
 * Contains the methods and properties related to the Polybar Taskbar module.
 */
class Polybar implements CommonFiles, CommonDirectories
{
    use \Core\Traits\CommonMethods;

    const POLYBAR_MAIN_CONFIG = AINAD_BASE_DIR.'/polybar/bar-main.ini';

    /**
     * __construct
     *
     * @return void
     */
    public function __construct(){}
    
    /**
     * storePolybarPids
     *
     * @return void
     */
    public function storePolybarPids(): void
    {
        exec("pgrep polybar", $pids);
        FileManager::writePhpVar(self::POLYBAR_DATA, ['bg' => $pids[0], 'main' => $pids[1], 'status' => true]);
    }

    public function setLocale(): void
    {
        $locale = str_replace('LANG=', '', exec("locale | grep 'LANG='"));

        $iniData = new IniParser(self::POLYBAR_MAIN_CONFIG);
        $iniData->setData('bar/main', 'locale', $locale);
        $iniData->writeFile();
    }

    /**
     * hideOnFullscreen
     *
     * @return void
     */
    public function hideOnFullscreen()
    {
        $this->setActiveWindow();
        $this->setPolybarPids();

        $windowStatus = explode(' = ', exec('xprop -id '.$this->activeWindow.' _NET_WM_STATE'))[1] ?? null;
        
        if ($windowStatus == '_NET_WM_STATE_FULLSCREEN') {
            if ($this->polybarData['status']) {
                exec('polybar-msg cmd hide');
                $this->polybarData['status'] = false;
                FileManager::writePhpVar(self::POLYBAR_DATA, $this->polybarData);
            }
        } else {
            if (!$this->polybarData['status']) {
                exec('
                    polybar-msg -p '.$this->polybarData['bg'].' cmd show;
                    polybar-msg -p '.$this->polybarData['main'].' cmd show;
                ');
                $this->polybarData['status'] = true;
                FileManager::writePhpVar(self::POLYBAR_DATA, $this->polybarData);
            }
        }
    }
}
