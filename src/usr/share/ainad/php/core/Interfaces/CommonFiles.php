<?php

namespace Core\Interfaces;

/**
 * Contains the methods and properties related to the Polybar Taskbar module.
 */
interface CommonFiles
{
    /**
     * Path to the file that stores all program available icons to be displayed
     * on the taskbar.
     */
    const ALL_ICONS = BASE_DIR.'/files/task-bar/all-icons.php';

    /**
     * Path to the file that stores cached icons of programs. This ensure that
     * the taskbar module will look for icons of programs that are installed in
     * the computer, instead of looking for all of them (which would include
     * programs that are not installed).
     */
    const CACHED_ICONS = BASE_DIR.'/files/task-bar/cached-icons.php';

    /**
     * Path to the file that stores all program available icons to be displayed
     * on the taskbar.
     */
    const POLYBAR_DATA = BASE_DIR.'/files/polybar-data.php';

    /**
     * Path to the file that stores system and OS data.
     */
    const SYSTEM_DATA = BASE_DIR.'/files/system-data.php';
}
