<?php

namespace Core\Controllers;

use \Core\Classes\FileManager;
use \Core\Interfaces\CommonFiles;
use \Core\Interfaces\CommonDirectories;


class TaskBar implements CommonFiles, CommonDirectories
{
    use \Core\Traits\CommonMethods;

    /**
     * @var string $currentWorkspace    Stores the active workspace ID to show
     *                                  the taskbar icons of the right
     *                                  workspace.
     */
    private string $currentWorkspace;

    /**
     * @var array $windowList           Stores the open windows returned by the
     *                                  Wmctrl command. Each index stores a line
     *                                  of the command.
     */
    private array $windowsList = [];
    
    /**
     * @var array $allIcons             Stores the icon theme.
     */
    private array $allIcons;

    /**
     * @var array $cachedIcons          Stores the icons of programs that were
     *                                  already used. This makes the search of
     *                                  icons a bit faster.
     */
    private array $cachedIcons;

    /**
     * @var array $ignoredAppClasses    Stores the list of ignored windows.
     */
    private array $ignoredAppClasses = [
        'polybar.Polybar',
    ];

    public  function __construct(){}
    
    /**
     * Gets all the current tasks and apply Polybar token strings to make them
     * funcional. It loops through all windows list, checking if they are part of
     * the current workspace and looking for an icon to display.
     *
     * When there is no icon for that program, then it will show its app code
     * name. It is good because I can easily know which programs has icons
     * missing.
     *
     * @return string
     */
    public function getTasks(): string
    {
        $this->setActiveWindow();

        $currentTaskList = '';

        $this->currentWorkspace = explode(' ', exec('wmctrl -d | grep "*"'))[0];
        exec('wmctrl -lx', $this->windowsList);

        $this->allIcons = require(self::ALL_ICONS);
        $this->cachedIcons = file_exists(self::CACHED_ICONS) ? require(self::CACHED_ICONS) : [];

        /**
         * Each of the `windowList` index has an window data in a string format,
         * like this:
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
            if ($windowWorkspace == $this->currentWorkspace or ($windowWorkspace == -1 and array_search($appClassName, $this->ignoredAppClasses) === false)) {
                
                /**
                 * Searches for the task icon that matches the window class
                 * name. If there is no task icon available, then it will show
                 * the window class name instead.
                 */
                $appClassName = $this->searchTaskIcon($appClassName);

                /**
                 * Checks if the active window is equal to the current window
                 * ID. If so then the icon/class name will be displayed in white
                 * color, if not, it will be displayed in the base color and
                 * will get the action to minimize the window if the task get
                 * clicked. If not, then it will only get the action to bring
                 * the window to the front.
                 */
                if ($windowId == $this->activeWindow) {
                    $appClassName = '%{A1: xdotool windowminimize '.$windowId.' & disown:}%{F#ffffff}'.$appClassName.'%{F-}%{A}';
                } else {
                    $appClassName = '%{A1: wmctrl -ia '.$windowId.' & disown:}'.$appClassName.'%{A}';
                }

                /**
                 * Every task will get the action to close if it gets clicked by
                 * the middle button of the mouse.
                 */
                $currentTaskList .= ' %{A2: wmctrl -ia '.$windowId.' & wmctrl -ic '.$windowId.' & disown:}'.$appClassName.'%{A} ';
            }
        }

        return trim($currentTaskList);
    }

    /**
     * Calls the hook number zero of the main Polybar panel. This hook simply
     * execute the getTasks() method.
     *
     * hook-0 = "ainad-utilities 'TaskBar' 'getTasks'"
     * 
     * @return void
     */
    public function refreshTasks()
    {
        $this->setPolybarPids();

        exec('polybar-msg -p '.$this->polybarData['main'].' action "#task-bar.hook.0" > /dev/null &');
    }


    /**
     * Searches for the icon of the running programs.
     *
     * @param  string $appClassName     The class name of the window.
     * 
     * @return string
     */
    private function searchTaskIcon(string $appClassName): string
    {
        /**
         * There will be two stages of the search. The first will search if the
         * program has its icon stored in the cached icon list. The second stage
         * will search for the icon through the full icon list.
         */
        $secondStage = false;

        foreach ([$this->cachedIcons, $this->allIcons] as $taskList) {

            /**
             * On each stage, the task list of the current stage will be
             * compared with the class name of the window. If the task list has
             * a program that contains the window class name, then the class
             * name will be replaced by the icon.
             */
            foreach ($taskList as $title => $icon) {
                if (strpos($appClassName, $title) !== false) {

                    /**
                     * If the icon exists in the second stage, but not in the
                     * first, then the current icon (found in the second stage)
                     * will also be stored in the cache list.
                     */
                    if ($secondStage) {
                        $this->cachedIcons[$title] = $icon;
                        FileManager::writePhpVar(self::CACHED_ICONS, $this->cachedIcons);
                    }

                    /**
                     * There are 3 icon-fonts used in this utility:
                     * 
                     *  font-6 = "Aicons:size=12:weight=Bold;3"
                     *  font-7 = "Aicons:size=12:weight=Regular;3"
                     *  font-8 = "Font Awesome 6 Brands:size=12:weight=Regular;3"
                     */
                    /* 
                     * In Polybar, when we want to use a font with tokens, we
                     * need to use the font ID, which is equal to the number of
                     * the font plus 1. The default font is font-6, so its token
                     * ID is 7.
                     *
                     * When the $icon[1] has an token ID set, then it is used.
                     * If not, then the default ID (7) is used.
                     */
                    if (!isset($icon[1])) {
                        $icon[1] = 7;
                    }
                    /**
                     * Creates the string with the icon or text between the
                     * Polybar tokens that define the font ID.
                     */
                    return '%{T'.$icon[1].'}'.$icon[0].'%{T-}';
                }
            }

            $secondStage = true;
        }

        return $appClassName;
    }
}
