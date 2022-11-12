<?php

namespace Core\Traits;

/**
 * This trait has only common properties and methods to be used by various
 * classes.
 */
trait CommonMethods
{
    /**
     * @var string $activeWindow        Stores the active window ID.
     */
    private string $activeWindow;

    /**
     * @var array $polybarData          Stores the Polybar data, such as PIDs
     *                                  and if it is visible.
     */
    private array $polybarData;
    
    /**
     * Gets the current window ID (the window that is in focus).
     *
     * @return void
     */
    private function setActiveWindow(): void
    {
        $activeWindow = explode(' ', exec('xprop -root _NET_ACTIVE_WINDOW'))[4] ?? '0x0';
        $this->activeWindow = substr_replace($activeWindow, '0', 2, 0);
    }
    
    /**
     * Initiates the $polybarData property by getting the Polybar data stored in
     * the PHP configuration data.
     *
     * @return void
     */
    private function setPolybarPids(): void
    {
        $this->polybarData = require(self::POLYBAR_DATA);
    }
}
