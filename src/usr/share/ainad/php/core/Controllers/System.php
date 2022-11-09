<?php

namespace Core\Controllers;

use \Core\Classes\FileManager;
use \Core\Classes\IniParser;
use \Core\Interfaces\CommonFiles;

/**
 * Contains the methods and properties related to the Polybar Taskbar module.
 */
class System implements CommonFiles
{
    /**
     * __construct
     *
     * @param  mixed $client
     * @return void
     */
    public function __construct(){}

    public function setLocale(): void
    {
        $locale = str_replace('LANG=', '', exec("locale | grep 'LANG='"));
        $language = explode('.', $locale)[0] ?? $locale;

        $ainadLanguage = $language == 'pt_BR' ? 'pt_BR' : 'en_US';

        FileManager::writePhpVar(self::SYSTEM_DATA, [
            'locale' => $locale,
            'language' => $language,
            'ainadLanguage' => $ainadLanguage,
        ]);
    }
}
