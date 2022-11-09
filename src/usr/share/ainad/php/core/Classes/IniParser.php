<?php

namespace Core\Classes;

use \Core\Classes\FileManager;

/**
 * Contains the methods and properties related to the Polybar Taskbar module.
 */
class IniParser
{
    private array $iniData;
    private string $iniFile;

    /**
     * __construct
     *
     * @param  mixed $client
     * @return void
     */
    public function __construct(string $iniFile)
    {
        $this->iniFile = $iniFile;
        $this->loadData();
    }

    public function setData(string $section, string $parameter, /*mixed*/ $value): void
    {
        $this->iniData[$section][$parameter] = $value;
    }

    public function pregReplaceData(string $section, string $parameter, /*mixed*/ $pattern, /*mixed*/ $replacement): void
    {
        $value = preg_replace($pattern, $replacement, $this->iniData[$section][$parameter]);
        $this->iniData[$section][$parameter] = $value;
    }

    public function getData(?string $section = null, ?string $parameter = null)//: mixed
    {
        if ($section === null and $parameter === null) {
            return $this->iniData;
        }

        return $this->iniData[$section][$parameter];
    }

    public function loadData(): void
    {
        $this->iniData = [];
        $lastSection = 0;

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
