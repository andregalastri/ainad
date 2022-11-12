<?php

namespace Core\Controllers;

use \Core\Classes\FileManager;
use \Core\Classes\IniParser;

class Workspace
{
    const BROWSE_BG_THEME = AINAD_BASE_DIR.'/rofi/widgets/workspace/browse-backgrounds.rasi';
    const SET_BG_THEME = AINAD_BASE_DIR.'/rofi/widgets/workspace/set-background.rasi';
    const SELECTED_IMAGE_THEME = AINAD_BASE_DIR.'/rofi/widgets/workspace/selected-image.rasi';
    const BG_DIR_LIST = BASE_DIR.'/files/workspace/bg-directories.php';
    const LOGIN_SCREEN_DIR = '/usr/share/sddm/themes/sugar-candy';

    public function __construct(){}
    
    /**
     * Opens the screen with a list of available backgrounds.
     *
     * @return void
     */
    public function browseBackgrounds(): void
    {
        $fileList = [];

        /**
         * Receives the background directories from the directories.php. For
         * each directory we get the all the files and checks if the file is a
         * valid image (in other words, we check if its MIME type starts with
         * "image/").
         *
         * Each valid image file is stored in the $fileList array, using tokens
         * that are recognized by ROFI.
         */
        foreach(require(self::BG_DIR_LIST) as $path) {
            foreach (glob($path) as $file) {
                if (strpos(mime_content_type($file), 'image/') !== false) {
                    $fileList[] = $file.'\0icon\x1f'.$file;
                }
            }
        }

        /**
         * Opens ROFI showing the screen to waits the user to choose one of the
         * image files available.
         */
        $choice = exec('echo -e "'.implode("\n", $fileList).'" | rofi -dmenu -theme "'.self::BROWSE_BG_THEME.'"');

        /**
         * If the choice isn't empty, that means that the user chose an image,
         * so, the setBackground() method is executed to ask the user which
         * location the image will be set.
         */
        if (!empty($choice)) {
            $this->setBackground([$choice, 'keep']);
        }
    }
    
    /**
     * Open a ROFI screen asking the user the location where the image will be
     * placed.
     *
     * @param array $arg                Arguments from the command line or
     *                                  methods.
     *
     * @index string $arg[0]            The path of the image file.
     *
     * @index string $arg[1]            Defines if the browse background screen
     *                                  will be reopen if the user cancels the
     *                                  current ROFI screen.
     *
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

        /**
         * The path of the image file is set as an icon, to give the user an
         * preview of the image.
         */
        FileManager::writeFile(self::SELECTED_IMAGE_THEME, "icon-background-image {\n    filename: \"".$arg[0]."\";\n}");

        /**
         * Opens ROFI screen asking where the user will place the image.
         */
        $choice = exec('echo -e "'.implode("\n", $option).'" | rofi -dmenu -theme "'.self::SET_BG_THEME.'"');

        /**
         * The valid options are:
         *
         * - workspace: places the image as the background of the workspace.
         * - login: places the image as the background of the login screen.
         * - both: places the image as background on both places.
         */
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

            /**
             * If the user cancels or clicks outside the ROFI screen, then it
             * tests if the $arg[1] is defined as 'keep'. If so, it reopens the
             * background browse screen. If not, it just closes the current ROFI
             * screen.
             */
            default:
                if (isset($arg[1]) and $arg[1] == 'keep') {
                    $this->browseBackgrounds();
                }
        }
    }
    
    /**
     * Sets the image file on the workspace using the Nitrogen application.
     *
     * @param  string $imageFile        The path of the image file.
     * 
     * @return void
     */
    private function setOnWorkspace(string $imageFile): void
    {
        exec('nitrogen --save --set-zoom-fill "'.$imageFile.'"');
    }
    
    /**
     * Sets the image file on the login screen. All it does is to copy the image
     * in the SDDM folder theme and update the theme config to make sure the
     * file name and extension matches the chosen image.
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
