<?php

namespace Core\Controllers;

use \Core\Classes\IniParser;
use \Core\Interfaces\CommonFiles;

class HardwareDefinition implements CommonFiles
{
    const NETWORK_INI_FILE = AINAD_BASE_DIR.'/polybar/modules/network.ini';
    
    public  function __construct(){}
        
    /**
     * This method sets the active network interface and the Dmenu action that
     * will be displayed when it gets clicked (based on the current system
     * language).
     *
     * @return void
     */
    public function setNetworkInterface(): void
    {
        /**
         * Gets the list of network interfaces available in the system.
         */
        exec("ip -br -c link show | grep -v '00:00:00:00:00:00'", $interfaceList);

        /**
         * Import the INI file to update it with every network interface found.
         */
        $config = new IniParser(self::NETWORK_INI_FILE);

        $count = 1;

        foreach ($interfaceList as $dataString) {
            
            /**
             * On each interface found, we will get an string data like this:
             *
             * eno1             UP             2c:f0:5d:38:80:3c <BROADCAST,MULTICAST,UP,LOWER_UP>
             *
             * The data that we want is the first one (eno1 for example). So, we
             * split the string using an empty space as delimiter and extract
             * the first element.
             *
             * For some reason, the extracted data comes with a strange
             * character [36m. Because of that, we trim out any of these
             * characters and possible empty spaces to make sure that we got
             * only the interface name.
             */
            $interface = explode(' ', $dataString);
            $interface = trim(ltrim($interface[0], '[36m'));
            
            /**
             * Every interface will be stored as an independent Polybar module,
             * so, they are stored serialized, starting from 1 and incrementing
             * until the last interface.
             */
            $module = 'module/network-'.$count;

            /**
             * Sets the data in the $config INI Parser object.
             */
            $config->setData($module, 'interface', $interface);
            $config->setData($module, 'type', 'internal/network');
            $config->setData($module, 'interval', '1');
            $config->setData($module, 'accumulate-stats', true);
            $config->setData($module, 'unknown-as-up', true);
            $config->setData($module, 'label-connected', '"%downspeed%"');
            $config->setData($module, 'label-disconnected', '"Offline"');
            $config->setData($module, 'format-connected', '"<label-connected>"');
            $config->setData($module, 'format-connected-prefix', '"  "');
            $config->setData($module, 'format-disconnected', '"<label-disconnected>"');
            $config->setData($module, 'format-disconnected-prefix', '"  "');
            
            $count++;
        }

        /**
         * Saves the INI file.
         */
        $config->writeFile();
    }
}
