<?php

namespace Core\Controllers;

use \Core\Classes\FileManager;

class PowerManager
{
    const POWER_MENU_THEME = AINAD_BASE_DIR.'/rofi/widgets/power-menu/power-menu.rasi';
    const DIALOG_BASE_THEME = AINAD_BASE_DIR.'/rofi/widgets/dialog/dialog-base.rasi';
    const DIALOG_DYNAMIC_THEME = AINAD_BASE_DIR.'/rofi/widgets/dialog/dialog-dynamic.rasi';
    const ASSETS_DIR = AINAD_BASE_DIR.'/rofi/assets';

    public function __construct(){}
    
    /**
     * Opens the power menu using ROFI.
     *
     * @return void
     */
    public function openMenu(): void
    {
        /**
         * Returns the icon of a specific font family using Pango markup and the
         * label that describes the option.
         *
         * @param string $icon          The icon that represents the option.
         *
         * @param string label          The label that represents the option.
         *
         * @return void
         */
        $baseOptionStyle = function (string $icon, string $label): string {
            return "<span font_family='Font Awesome 6 Pro' weight='bold'>".$icon."</span> ".$label;
        };

        /**
         * Navigation options.
         */
        $option = [
            'logoff' => $baseOptionStyle('', ' Encerrar sessão'),
            'reboot' => $baseOptionStyle('', ' Reiniciar'),
            'poweroff' => $baseOptionStyle('', ' Desligar'),
        ];

        /**
         * Gets the uptime of the current session. It translates the string if
         * needed.
         */
        $uptime = trim(str_replace(['up', 'hour', 'minute', 'day'],['Ativo há', 'hora', 'minuto', 'dia'], exec('uptime -p')));

        /**
         * Runs ROFI to display the power menu and waits for the user to choose
         * one option.
         */
        $choice = exec('echo -e "'.implode("\n", $option).'" | rofi -dmenu -markup-rows -p "'.$uptime.'" -theme "'.self::POWER_MENU_THEME.'"');

        /**
         * If the choice matches one of the defined options below, then an
         * dialog is open asking to the user to confirm the action. Any other
         * value (or if the user clicks outside) closes the menu.
         */
        switch($choice) {
            case $option['poweroff']:
                $this->openDialog(['poweroff']);
            break;

            case $option['reboot']:
                $this->openDialog(['reboot']);
            break;

            case $option['logoff']:
                $this->openDialog(['logoff']);
            break;
        }
    }
    
    /**
     * Opens the dialog asking if the user confirms the action chose.
     *
     * @param  array $arg               Arguments received by the method:
     *                                  [
     *                                      0: The action chose. Can be
     *                                      'poweroff', 'reboot' or 'logoff'.
     *                                  ]

     *
     * @return void
     */
    public function openDialog(array $arg): void
    {
        /**
         * Dialog options.
         */
        $option = [
            'yes' => 'Sim',
            'no' => 'Não',
        ];

        /**
         * Messages asking the user to confirm the action.
         */
        $message = [
            'poweroff' => 'Deseja realmente desligar o computador?',
            'reboot' => 'Deseja realmente reiniciar o computador?',
            'logoff' => 'Deseja realmente encerrar esta sessão?',
        ];

        /**
         * Checks if the $arg[0] has a valid option, that are 'poweroff',
         * 'reboot' or 'logoff' (defined in the $message array). If the value is
         * invalid, the dialog will not pop up.
         */
        if (array_key_exists($arg[0], $message)) {

            /**
             * Creates a ROFI theme file with the proper description and icon,
             * based on the action the user chose.
             */
            FileManager::writeFile(self::DIALOG_DYNAMIC_THEME, "window {\n    width: 320px;\n}\n\nicon-description {\n    filename: \"".self::ASSETS_DIR."/".$arg[0].".svg\";\n}\n");

            /**
             * Launch ROFI using the theme created above and waits the user
             * choose one of the dialog options.
             */
            $choice = exec('echo -e "'.implode("\n", $option).'" | rofi -dmenu -mesg "'.$message[$arg[0]].'" -theme "'.self::DIALOG_BASE_THEME.'"');
    
            /**
             * If the option is equal to 'yes', then it will execute the action.
             */
            if ($choice == $option['yes']) {
                switch($arg[0]) {
                    case 'poweroff':
                        $this->poweroff();
                    break;
        
                    case 'reboot':
                        $this->reboot();
                    break;
        
                    case 'logoff':
                        $this->logoff();
                    break;
                }
            }
        }
    }

    /**
     * Has actions that will be executed before the final action, like close
     * apps safely for example.
     *
     * @return void
     */
    private function firstActions(): void
    {
        /**
         * Calls the logoff.bash script, which runs the user logoff commands as
         * well.
         */
        exec(AINAD_BASE_DIR.'/openbox/logoff.bash');
    }
    
    /**
     * Power off the computer.
     *
     * @return void
     */
    private function poweroff(): void
    {
        $this->firstActions();
        exec('systemctl poweroff > /dev/null &');
    }
    
    /**
     * Reboot the computer.
     *
     * @return void
     */
    private function reboot(): void
    {
        $this->firstActions();
        exec('systemctl reboot > /dev/null &');
    }
    
    /**
     * Logs off the current session.
     *
     * @return void
     */
    private function logoff(): void
    {
        $this->firstActions();
        exec('openbox --exit > /dev/null &');
    }
}
