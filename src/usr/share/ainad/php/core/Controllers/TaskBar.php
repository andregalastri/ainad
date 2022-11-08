<?php

namespace Core\Controllers;

use \Core\Classes\FileManager;
use \Core\Interfaces\CommonFiles;
use \Core\Interfaces\CommonDirectories;

/**
 * Contains the methods and properties related to the Polybar Taskbar module.
 */
class TaskBar implements CommonFiles, CommonDirectories
{
    use \Core\Traits\CommonMethods;

    /**
     * @var string $currentWorkspace    Stores the active workspace ID to show
     *                                  the taskbar to the right monitor.
     */
    private string $currentWorkspace;

    /**
     * @var array $windowList           Stores the window list returned by the
     *                                  wmctrl command. Each index stores a line
     *                                  of the command.
     */
    private array $windowsList = [];
    
    /**
     * @var array $allIcons             Stores all the program icons to be
     *                                  displayed in the taskbar.
     */
    private array $allIcons;

    /**
     * @var array $cachedIcons          Stores the icons of programs that were
     *                                  already used. This makes the search of
     *                                  icons a bit faster.
     */
    private array $cachedIcons;

    /**
     * Initializes the properties by running some Bash commands or importing
     * some files.
     *
     * @return void
     */    
    public  function __construct()
    {
        $this->setActiveWindow();
        $this->setPolybarPids();
    }
    
    /**
     * Gets all the current tasks and apply some Polybar tokens to make them
     * funcional. It loops through all window list, checking if they are part of
     * the current workspace and looking for an icon to display.
     *
     * When the is no icon for that program, then it will show its app code
     * name. It is good because I can easily know which programs has icons
     * missing.
     *
     * @return string
     */
    public function getTasks(): string
    {
        $currentTaskList = '';

        $this->currentWorkspace = explode(' ', exec('wmctrl -d | grep "*"'))[0];
        exec('wmctrl -lx', $this->windowsList);

        $this->allIcons = require(self::ALL_ICONS);
        $this->cachedIcons = file_exists(self::CACHED_ICONS) ? require(self::CACHED_ICONS) : [];

        /**
         * Each of the $windowList property index has an window data in string
         * format like this:
         *
         * 0x0340000a   0         nemo.Nemo      ainad-pc1 Pasta pessoal
         *
         * We need to to split this string into array values.
         */
        foreach ($this->windowsList as $line => $data) {
            $data = explode(' ', $data);
            $data = array_values(array_filter($data, 'strlen'));

            /**
             * For better readability, each of the important data is stored in
             * variables.
             */
            $windowId = $data[0];
            $windowWorkspace = $data[1];
            $appClassName = $data[2];

            /**
             * Only windows that are in the current workspace will be shown.
             */
            if ($windowWorkspace == $this->currentWorkspace) {
                
                /**
                 * Searches for the task icon of the window class name. If there
                 * is no task icon available, then it will show the window class
                 * name instead.
                 */
                $appClassName = $this->searchTaskIcon($appClassName);

                /**
                 * Checks if the active window is equal to the current window
                 * ID. If so then the icon/class name will be displayed in white
                 * color, if not, it will be displayed in the current color.
                 *
                 * Also, if the task is the current active window, it will get
                 * the action to minimize the window if the task get clicked. If
                 * not, then it will get the action to bring the window to the
                 * front.
                 */
                if ($windowId == $this->activeWindow) {
                    $appClassName = '%{A1: xdotool windowminimize '.$windowId.' & disown:}%{F#ffffff}'.$appClassName.'%{F-}%{A}';
                } else {
                    $appClassName = '%{A1: wmctrl -ia '.$windowId.' & disown:}'.$appClassName.'%{A}';
                }

                $currentTaskList .= ' %{A2: wmctrl -ia '.$windowId.' & wmctrl -ic '.$windowId.' & disown:}'.$appClassName.'%{A} ';
            }
        }

        return trim($currentTaskList);
    }

    /**
     * refreshTasks
     *
     * @return void
     */
    public function refreshTasks()
    {
        exec('polybar-msg -p '.$this->polybarData['main'].' action "#task-bar.hook.0" > /dev/null &');
    }


    /**
     * Searches for the icon of the programs running.
     *
     * @param  string $appClassName     The class name of the window.
     * 
     * @return string
     */
    private function searchTaskIcon(string $appClassName): string
    {
        /**
         * There will be two stages of the search. The first will search if the
         * program has its icon stores in the cached icon list. The second stage
         * will search for the icon through the full icon list.
         */
        $secondStage = false;

        foreach ([$this->cachedIcons, $this->allIcons] as $taskList) {

            /**
             * On each stage, the task list of the current stage will be
             * compared with the class name of the window. If the task list has
             * an program that contains the window class name, then the class
             * name will be replaced by the icon.
             */
            foreach ($taskList as $title => $icon) {
                if (strpos($appClassName, $title) !== false) {

                    /**
                     * If the icon exists on the second stage but not in the
                     * first, then the current icon (found in the second stage)
                     * will be also stored as a cached icon.
                     */
                    if ($secondStage) {
                        $this->cachedIcons[$title] = $icon;
                        FileManager::writePhpVar(self::CACHED_ICONS, $this->cachedIcons);
                    }

                    if (!isset($icon[1])) {
                        $icon[1] = '%{T8}';
                    }
                    return $icon[1].$icon[0].'%{T-}';
                }
            }

            $secondStage = true;
        }

        return $appClassName;
    }
}
