<?php

namespace Core\Traits;

trait CommonMethods
{
    /**
     * @var string $activeWindow        Stores the active window ID.
     */
    private string $activeWindow;

    /**
     * 
     */
    private array $polybarData;
    
    /**
     * setActiveWindow
     *
     * @return void
     */
    private function setActiveWindow(): void
    {
        $activeWindow = explode(' ', exec('xprop -root _NET_ACTIVE_WINDOW'))[4] ?? '0x0';
        $this->activeWindow = substr_replace($activeWindow, '0', 2, 0);
    }
    
    /**
     * getPolybarPids
     *
     * @return void
     */
    private function setPolybarPids(): void
    {
        $this->polybarData = require(self::POLYBAR_DATA);
    }
}
