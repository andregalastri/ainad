<?php

namespace Core\Classes;

use \Core\Classes\FileManager;

class IniParser
{
    /**
     * @var array $iniData              Stores the information parsed from the
     *                                  INI file. Its structure will be like
     *                                  this:
     *
     *                                  ['section-name' => ['parameter' => 'value']];
     */
    private array $iniData;

    /**
     * @var string $iniFile             The file path to the INI file.
     */
    private string $iniFile;

    /**
     * Initiates the property $iniFile storing the file path and loading it.
     *
     * @param string $iniFile           The file path to the INI file.
     * 
     * @return void
     */
    public function __construct(string $iniFile)
    {
        $this->iniFile = $iniFile;
        $this->loadData();
    }
    
    /**
     * Adds data in the $iniData property. If the same data exists, updates it.
     *
     * @param string $section           The section where the parameter will be
     *                                  added or updated.
     *
     * @param string $parameter         The parameter that will be added or
     *                                  updated.
     *
     * @param mixed $value              The value of the parameter.
     *
     * @return void
     */
    public function setData(string $section, string $parameter, /*mixed*/ $value): void
    {
        $this->iniData[$section][$parameter] = $value;
    }

    /**
     * Removes data from the $iniData property.
     * 
     * @param string $section           The section where the parameter is.
     *
     * @param string $parameter         The parameter that will be removed.
     */
    public function removeData(string $section, string $parameter): void
    {
        unset($this->iniData[$section][$parameter]);
    }
    
    /**
     * Allows to update the value of a parameter using regex.
     *
     * @param string $section           The section where the parameter's value
     *                                  will be replaced.
     *
     * @param string $parameter         The parameter that has the value that
     *                                  will be replaced.
     *
     * @param string $pattern           Regex pattern that will be search in the
     *                                  parameter value.
     *
     * @param string $replacement       The string that will replace matches.
     *
     * @return void
     */
    public function pregReplaceData(string $section, string $parameter, string $pattern, string $replacement): void
    {
        $value = preg_replace($pattern, $replacement, $this->iniData[$section][$parameter]);
        $this->iniData[$section][$parameter] = $value;
    }
    
    /**
     * Gets the data from the parsed INI. If all parameters 
     *
     * @param null|string $section      The section where the parameter will be
     *                                  added or updated.
     * 
     * @param null|string $parameter    The parameter that will be added or
     *                                  updated.
     * 
     * @return array|string
     */
    public function getData(?string $section = null, ?string $parameter = null)//: mixed
    {
        if ($section === null and $parameter === null) {
            return $this->iniData;
        }

        return $this->iniData[$section][$parameter];
    }
    
    /**
     * Loads the INI file and converts its sections, parameters and values to an
     * multimensional associative array.
     *
     * @return void
     */
    public function loadData(): void
    {
        $this->iniData = [];
        $lastSection = 0;

        /**
         * On each line of the file it will search for the section (the text
         * between square brackets) and parameters.
         * 
         * It ignores comments.
         */
        foreach (new \SplFileObject($this->iniFile) as $line) {
            $line = trim($line);

            if (preg_match('/^\[(.*?)\]/', $line, $match) === 1) {
                $lastSection = $match[1];
                $this->iniData[$lastSection] = [];
                continue;
            }

            if (preg_match('/^([a-zA-Z].*?)=(.*)/', $line, $match) === 1) {
                $this->iniData[$lastSection][trim($match[1])] = trim($match[2]);
                continue;
            }
        }
    }
    
    /**
     * Saves the INI data into a file.
     *
     * @param  null|string $file        The file location which the data will be
     *                                  stored. If no file is specified here, it
     *                                  will save the data into the initial
     *                                  loaded file, overwritting its contets.
     *
     * @return void
     */
    public function writeFile(?string $file = null): void
    {
        $secondPass = false;
        foreach ($this->iniData as $section => $parameterList) {
            if ($secondPass) {
                $iniData[] = "\n";
            }

            $iniData[] = '['.$section."]";

            foreach ($parameterList as $key => $value) {
                $iniData[] = $key.' = '.$value;
            }

            $secondPass = true;
        }

        FileManager::writeFile($file ?? $this->iniFile, implode("\n", $iniData));
    }
}
