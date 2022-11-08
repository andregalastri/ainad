<?php

namespace Core\Controllers;

use \Core\Classes\IniParser;

/**
 * Contains the methods and properties related to the Polybar Taskbar module.
 */
class HardwareDefinition
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

        $config = new IniParser(self::NETWORK_INI_FILE);
        $config->setData('module/network', 'interface', $interface);
        $config->writeFile();
    }
}
