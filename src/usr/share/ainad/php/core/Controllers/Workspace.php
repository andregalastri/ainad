<?php

namespace Core\Controllers;

use \Core\Classes\FileManager;
use \Core\Classes\IniParser;

/**
 * Contains the methods and properties related to the Polybar Taskbar module.
 */
class Workspace
{
    const BROWSE_BG_THEME = AINAD_BASE_DIR.'/rofi/widgets/workspace/browse-backgrounds.rasi';
    const SET_BG_THEME = AINAD_BASE_DIR.'/rofi/widgets/workspace/set-background.rasi';
    const SELECTED_IMAGE_THEME = AINAD_BASE_DIR.'/rofi/widgets/workspace/selected-image.rasi';
    const BG_DIR = AINAD_BASE_DIR.'/backgrounds';
    const LOGIN_SCREEN_DIR = '/usr/share/sddm/themes/sugar-candy';

    /**
     * __construct
     *
     * @param  mixed $client
     * @return void
     */
    public function __construct(){}
    
    /**
     * browseBackgrounds
     *
     * @return void
     */
    public function browseBackgrounds(): void
    {
        $fileList = [];

        foreach (glob(self::BG_DIR.'/*') as $file) {
            if (strpos(mime_content_type($file), 'image/') !== false) {
                $fileList[] = $file.'\0icon\x1f'.$file;
            }
        }

        $choice = exec('echo -e "'.implode("\n", $fileList).'" | rofi -dmenu -theme "'.self::BROWSE_BG_THEME.'"');

        if (!empty($choice)) {
            $this->setBackground([$choice, 'keep']);
        }
    }
    
    /**
     * setBackground
     *
     * @param  mixed $arg
     * @return void
     */
    public function setBackground(array $arg): void
    {
        $option = [
            'workspace' => 'Na área de trabalho',
            'login' => 'Na tela de login',
            'both' => 'Em ambas telas',
            'cancel' => 'Cancelar'
        ];

        FileManager::writeFile(self::SELECTED_IMAGE_THEME, "icon-background-image {\n    filename: \"".$arg[0]."\";\n}");

        $choice = exec('echo -e "'.implode("\n", $option).'" | rofi -dmenu -theme "'.self::SET_BG_THEME.'"');

        switch($choice) {
            case $option['workspace']:
                $this->setOnWorkspace($arg[0]);
            break;

            case $option['login']:
                $this->setOnLoginScreen($arg[0]);
            break;

            case $option['both']:
                $this->setOnWorkspace($arg[0]);
                $this->setOnLoginScreen($arg[0]);
            break;

            default:
                if (isset($arg[1]) and $arg[1] == 'keep') {
                    $this->browseBackgrounds();
                }
        }
    }
    
    /**
     * setOnWorkspace
     *
     * @param  mixed $filePath
     * @return void
     */
    private function setOnWorkspace(string $filePath): void
    {
        exec('nitrogen --save --set-zoom-fill "'.$filePath.'"');
    }
    
    /**
     * setOnLoginScreen
     *
     * @param  mixed $filePath
     * @return void
     */
    private function setOnLoginScreen(string $filePath): void
    {
        $pathInfo = pathinfo($filePath);
        $destination = self::LOGIN_SCREEN_DIR.'/background.'.$pathInfo['extension'];
        copy($filePath, $destination);

        $config = new IniParser(self::LOGIN_SCREEN_DIR.'/theme.conf');
        $config->setData('General', 'Background', '"'.$destination.'"');
        $config->writeFile();
    }
}
