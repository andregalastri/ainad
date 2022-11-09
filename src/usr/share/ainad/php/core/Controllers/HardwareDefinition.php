<?php

namespace Core\Controllers;

use \Core\Classes\IniParser;
use \Core\Interfaces\CommonFiles;

/**
 * Contains the methods and properties related to the Polybar Taskbar module.
 */
class HardwareDefinition implements CommonFiles
{
    const NETWORK_INI_FILE = AINAD_BASE_DIR.'/polybar/modules/network.ini';
    
    /**
     * __construct
     *
     * @param  mixed $client
     * @return void
     */
    public  function __construct()
    {
        $this->setNetworkInterface();
    }
        
    /**
     * setNetworkInterface
     *
     * @return void
     */
    public function setNetworkInterface(): void
    {
        exec("ip -br -c link show | grep -v '00:00:00:00:00:00'", $interfaceList);
        $interface = explode(' ', $interfaceList[0]);
        $interface = ltrim($interface[0], '[36m');

        $system = require(self::SYSTEM_DATA);
        $action = 'networkmanager_dmenu_'.$system['ainadLanguage'];
        $regexPattern = '/("%{A1:)(.*?)(\s&:}.*)/';

        $config = new IniParser(self::NETWORK_INI_FILE);
        $config->pregReplaceData('module/network', 'format-connected', $regexPattern, '$1'.$action.'$3');
        $config->pregReplaceData('module/network', 'format-connected-prefix', $regexPattern, '$1'.$action.'$3');
        $config->pregReplaceData('module/network', 'format-disconnected', $regexPattern, '$1'.$action.'$3');
        $config->pregReplaceData('module/network', 'format-disconnected-prefix', $regexPattern, '$1'.$action.'$3');
        $config->setData('module/network', 'interface', $interface);
        $config->writeFile();
    }
}
