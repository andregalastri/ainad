<?php

namespace Core\Controllers;

use \Core\Classes\FileManager;

/**
 * Contains the methods and properties related to the Polybar Taskbar module.
 */
class PowerManager
{
    const POWER_MENU_THEME = AINAD_BASE_DIR.'/rofi/widgets/power-menu/power-menu.rasi';
    const DIALOG_BASE_THEME = AINAD_BASE_DIR.'/rofi/widgets/dialog/dialog-base.rasi';
    const DIALOG_DYNAMIC_THEME = AINAD_BASE_DIR.'/rofi/widgets/dialog/dialog-dynamic.rasi';
    const ASSETS_DIR = AINAD_BASE_DIR.'/rofi/assets';

    /**
     * __construct
     *
     * @param  mixed $client
     * @return void
     */
    public function __construct(){}
    
    /**
     * openMenu
     *
     * @param  mixed $arg
     * @return void
     */
    public function openMenu(): void
    {
        $getLabel = function(string $icon, string $label): string
        {
            return "<span font_family='Font Awesome 6 Pro' weight='bold'>".$icon."</span> ".$label;
        };

        $getOption = function(string $icon, string $label): string
        {
            return $label.'\0icon\x1f'.self::ASSETS_DIR.'/'.$icon;
        };

        $label = [
            'logoff' => $getLabel('', ' Encerrar sessão'),
            'reboot' => $getLabel('', ' Reiniciar'),
            'poweroff' => $getLabel('', ' Desligar'),
        ];

        $option = [
            'logoff' => $getOption('exit-light.svg', $label['logoff']),
            'reboot' => $getOption('refresh-light.svg', $label['reboot']),
            'poweroff' => $getOption('power-off-light.svg', $label['poweroff']),
        ];

        $uptime = trim(str_replace(['up', 'hour', 'minute', 'day'],['', 'hora', 'minuto', 'dia'], exec('uptime -p')));

        $choice = exec('echo -e "'.implode("\n", $option).'" | rofi -dmenu -markup-rows -p "Ativo há '.$uptime.'" -theme "'.self::POWER_MENU_THEME.'"');

        switch($choice) {
            case $label['poweroff']:
                if ($this->openDialog(['poweroff'])) {
                    $this->shutdown();
                };
            break;

            case $label['reboot']:
                if ($this->openDialog(['reboot'])) {
                    $this->reboot();
                };
            break;

            case $label['logoff']:
                if ($this->openDialog(['logoff'])) {
                    $this->logoff();
                };
            break;
        }
    }
    
    /**
     * openDialog
     *
     * @param  mixed $arg
     * @return void
     */
    public function openDialog(array $arg): bool
    {
        $option = [
            'yes' => 'Sim',
            'no' => 'Não',
        ];

        $message = [
            'poweroff' => 'Deseja realmente desligar o computador?',
            'reboot' => 'Deseja realmente reiniciar o computador?',
            'logoff' => 'Deseja realmente encerrar esta sessão?',
        ];

        if (array_key_exists($arg[0], $message)) {
            FileManager::writeFile(self::DIALOG_DYNAMIC_THEME, "window {\n    width: 320px;\n}\n\nicon-description {\n    filename: \"".self::ASSETS_DIR."/".$arg[0].".svg\";\n}\n");

            $choice = exec('echo -e "'.implode("\n", $option).'" | rofi -dmenu -mesg "'.$message[$arg[0]].'" -theme "'.self::DIALOG_BASE_THEME.'"');
    
            switch($choice) {
                case $option['yes']:
                    return true;
                break;
            }
        }

        return false;
    }
    
    /**
     * beforeAction
     *
     * @return void
     */
    private function beforeAction(): void
    {
        exec(AINAD_BASE_DIR.'/openbox/logoff.bash');
    }
    
    /**
     * shutdown
     *
     * @return void
     */
    private function shutdown(): void
    {
        $this->beforeAction();
        exec('systemctl poweroff > /dev/null &');
    }
    
    /**
     * reboot
     *
     * @return void
     */
    private function reboot(): void
    {
        $this->beforeAction();
        exec('systemctl reboot > /dev/null &');
    }
    
    /**
     * logoff
     *
     * @return void
     */
    private function logoff(): void
    {
        $this->beforeAction();
        exec('openbox --exit > /dev/null &');
    }
}
