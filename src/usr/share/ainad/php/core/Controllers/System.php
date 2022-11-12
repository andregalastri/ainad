<?php

namespace Core\Controllers;

use \Core\Classes\FileManager;
use \Core\Classes\IniParser;
use \Core\Interfaces\CommonFiles;

/**
 * This class executes methods concerning system stuff, like timezone and
 * language locales.
 */
class System implements CommonFiles
{
    private array $systemData;

    /**
     * Checks if the system data file exists. If so, initiates the $systemData
     * property with the data in that file. If not, it initiates the property
     * empty.
     *
     * @return void
     */
    public function __construct()
    {
        $this->systemData = file_exists(self::SYSTEM_DATA) ? require(self::SYSTEM_DATA) : [];
    }
    
    /**
     * Gets and store the current locale and language of the system. It runs on
     * startup.
     *
     * @return void
     */
    public function setLocale(): void
    {
        $locale = str_replace('LANG=', '', exec("locale | grep 'LANG='"));
        $language = explode('.', $locale)[0] ?? $locale;

        $ainadLanguage = $language == 'pt_BR' ? 'pt_BR' : 'en_US';

        $this->systemData['locale'] = $locale;
        $this->systemData['language'] = $language;
        $this->systemData['ainadLanguage'] = $ainadLanguage;

        $this->writeFile();
    }
    
    /**
     * Gets and store the current timezone of the system. It runs on startup.
     *
     * @return void
     */
    public function setTimezone(): void
    {
        /**
         * If the timezone from timedatectl command is not defined, it uses UTC
         * as default.
         */
        $timezone = explode('=', exec('timedatectl show | grep "Timezone"'))[1] ?? 'UTC';

        $this->systemData['timezone'] = $timezone;

        $this->writeFile();
    }
    
    /**
     * Saves the data stored in the $systemData property.
     *
     * @return void
     */
    private function writeFile(): void
    {
        FileManager::writePhpVar(self::SYSTEM_DATA, $this->systemData);
    }
}
