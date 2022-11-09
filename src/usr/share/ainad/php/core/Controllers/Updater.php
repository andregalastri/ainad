<?php

namespace Core\Controllers;

use \Core\Classes\FileManager;
use \Core\Interfaces\CommonFiles;
use \Core\Interfaces\CommonDirectories;

/**
 * Contains the methods and properties related to the Polybar Taskbar module.
 */
class Updater implements CommonFiles, CommonDirectories
{
    use \Core\Traits\CommonMethods;

    const UPDATER_BASE_THEME = AINAD_BASE_DIR.'/rofi/widgets/updater/updater-base.rasi';
    const UPDATER_PAGINATION_THEME = AINAD_BASE_DIR.'/rofi/widgets/updater/updater-pagination.rasi';
    const UPDATER_PACKAGES_THEME = AINAD_BASE_DIR.'/rofi/widgets/updater/updater-packages.rasi';
    const UPDATER_ROFI_CMD = AINAD_BASE_DIR.'/rofi/widgets/updater/updater-rofi-cmd.bash';
    const APPLY_CMD = AINAD_BASE_DIR.'/rofi/widgets/updater/apply.bash';

    const PACKAGE_DATA = AINAD_BASE_DIR.'/php/files/updater/package-data.php';
    const PACKAGES_PER_PAGE = 10;

    private array $packageList = [];
    private int $currentPage = 1;

    /**
     * __construct
     *
     * @param  mixed $client
     * @return void
     */
    public function __construct()
    {
    }
    
    /**
     * checkUpdates
     *
     * @return string
     */
    public function checkUpdates(): string
    {
        exec('yay -Qua && checkupdates', $packageList);

        foreach ($packageList as $package) {
            $package = explode(' ', $package);

            $this->packageList[] = [
                'name' => $package[0],
                'oldVersion' => $package[1],
                'newVersion' => $package[3],
                'selected' => true,
            ];
        }

        if (!empty($this->packageList)) {
            $this->writePackageData();
            return '%{T5}%{T-}';
        }

        $this->writeUpToDateData();
        return '';
    }

    /**
     * launchUpdater
     *
     * @return void
     */
    public function launchUpdater(array $arg): void
    {
        // if (isset($arg[0]) and $arg[0] == 'recheckUpdates') {
        //     exec('dunstify -i "update-manager" -t 2000 "Gerenciador de atualizações"  "Por favor, aguarde..."');
        //     $this->checkUpdates();
        //     $this->polybarHook();
        // }

        exec('rofi -no-config -show updater -modi "updater:'.self::UPDATER_ROFI_CMD.'" -theme "'.self::UPDATER_BASE_THEME.'"');
    }

    public function polybarHook(): void
    {
        $this->setPolybarPids();
        exec('polybar-msg -p '.$this->polybarData['main'].' action "#check-updates.hook.0"');
    }
    
    /**
     * setPackageSelected
     *
     * @param  mixed $arg
     * @return void
     */
    public function setPackageSelected(array $arg): void
    {
        $this->getPackages();
        $selected = (self::PACKAGES_PER_PAGE * $this->currentPage) - self::PACKAGES_PER_PAGE + $arg[0];

        $this->packageList[$selected]['selected'] = !$this->packageList[$selected]['selected'];
        $this->writePackageData();
    }
    
    /**
     * setPage
     *
     * @param  mixed $arg
     * @return void
     */
    public function setPage(array $arg): void
    {
        $this->getPackages();
        
        if (trim($arg[0]) == 'next') {
            $this->currentPage += 1;
        } else {
            $this->currentPage -= 1;
        }

        $this->writePackageData();
    }
    
    /**
     * applyUpdates
     *
     * @return void
     */
    public function applyUpdates(): void
    {
        $this->setPolybarPids();
        $this->getPackages();

        $numberOfSelectedPackages = 0;
        $selectedPackages = [];
        foreach ($this->packageList as $packageData) {
            if ($packageData['selected']) {
                $numberOfSelectedPackages++;
                $selectedPackages[] = $packageData['name'];
            }
        }

        if ($numberOfSelectedPackages <= 0) {
            exec('dunstify -i "dialog-warning" -t 10000 "Atenção!" "É necessário selecionar ao menos um pacote de atualização."');
        } else {
            exec('killall -9 rofi');
            exec('polybar-msg -p '.$this->polybarData['main'].' action "#check-updates.hook.1"');
            
            $this->writeUpToDateData();
            
            exec('xfce4-terminal -e "'.self::APPLY_CMD.' \"'.implode(' ', $selectedPackages).'\""');
        }
    }
    
    /**
     * writePackageData
     *
     * @return void
     */
    private function writePackageData(): void
    {
        FileManager::writePhpVar(self::PACKAGE_DATA, [
            'list' => $this->packageList,
            'currentPage' => $this->currentPage,
        ]);

        $this->createPackageList();
        $this->createPagination();
    }
    
    /**
     * writeUpToDateData
     *
     * @return void
     */
    private function writeUpToDateData(): void
    {
        FileManager::writePhpVar(self::PACKAGE_DATA, [
            'list' => [],
            'currentPage' => 1,
        ]);

        FileManager::writeFile(self::UPDATER_PACKAGES_THEME,
        "icon-up-to-date {
            expand: false;
            filename: \"".self::ASSETS_DIR."/check.svg\";
            size: 90px;
        }
        
        textbox-up-to-date {
            font: \"RobotoCondensed 18px\";
            content: \"Seu sistema está atualizado!\";
            horizontal-align: 0.5;
        }
        
        up-to-date-container {
            children: [icon-up-to-date, textbox-up-to-date];
            background-color: transparent;
            padding: 70px 5px;
        }
        
        mainbox {
            children: [textbox-header-text, textbox-content-text, content-information, up-to-date-container, tools-bottom];
        }
        
        tools-bottom {
            children: [button-tools-close];
        }");
    }
    
    /**
     * getPackages
     *
     * @return void
     */
    private function getPackages(): void
    {
        if (empty($this->packageList)) {
            $packageData = require(self::PACKAGE_DATA);
            $this->packageList = $packageData['list'];
            $this->currentPage = $packageData['currentPage'];
        }
    }
    
    /**
     * createPackageList
     *
     * @return void
     */
    private function createPackageList(): void
    {
        for ($i = 0, $actionNumber = 1; $i < count($this->packageList); $i++, $actionNumber++) {

            $checkboxPath = self::ASSETS_DIR.'/square-check-solid.svg';
            $package = $this->packageList[$i];

            if ($package['selected'] == false) {
                $checkboxPath = self::ASSETS_DIR.'/square.svg';
            }

            $contentFile[] = "
            icon-status-$i {
                expand: false;
                filename: \"$checkboxPath\";
                size: 20px;
                cursor: pointer;
                action: \"kb-custom-$actionNumber\";
            }

            textbox-package-name-$i {
                content: \"".$package['name']."\";
            }

            textbox-package-old-version-$i {
                content: \"".$package['oldVersion']."\";
            }

            textbox-package-new-version-$i {
                text-color: #0b985d;
                content: \"".$package['newVersion']."\";
            }

            line-$i {
                orientation: horizontal;
                children: [icon-status-$i, textbox-package-name-$i, textbox-package-old-version-$i, textbox-package-new-version-$i];
                spacing: 5px;
            }";

            if ($actionNumber >= self::PACKAGES_PER_PAGE) {
                $actionNumber = 0;
            }
        }

        FileManager::writeFile(self::UPDATER_PACKAGES_THEME, implode("\n", $contentFile));
    }
    
    /**
     * createPagination
     *
     * @return void
     */
    private function createPagination(): void
    {
        $totalPackages = count($this->packageList);
        $totalPages = (int)ceil($totalPackages / self::PACKAGES_PER_PAGE);
        $pageEnd = $this->currentPage * self::PACKAGES_PER_PAGE;
        $pageStart = $pageEnd - self::PACKAGES_PER_PAGE;

        $linesToShow[] = 'line-label';

        for ($i = $pageStart; $i < $pageEnd; $i++) {
            if ($i >= $totalPackages) {
                break;
            }

            $linesToShow[] = 'line-'.$i;
        }

        $styles[] = "
        update-list {
            children: [".implode(',', $linesToShow)."];
        }\n";

        $selectedPackages = 0;

        foreach ($this->packageList as $package) {
            if ($package['selected'] == true) {
                $selectedPackages++;
            }
        }

        $updateLabels = [
            'newUpdates' => $totalPackages > 1 ? 'novas atualizações' : 'nova atualização',
            'selectedUpdates' => $totalPackages > 1 ? 'selecionadas' : 'selecionada',
        ];

        $styles[] = "
        textbox-number-of-packages {
            vertical-align: 0.5;
            content: \"".$totalPackages." ".$updateLabels['newUpdates']." | ".$selectedPackages." ".$updateLabels['selectedUpdates']." \";
        }\n";

        $styles[] = "
        textbox-pagination {
            vertical-align: 0.5;
            expand: false;
            content: \"".$this->currentPage." de ".$totalPages."\";
        }\n";

        if ($totalPages > 1) {

            if ($this->currentPage == 1) {
                $navigationDefinitions = "children: [textbox-number-of-packages, textbox-pagination, button-tools-next];\n";
            } elseif ($this->currentPage > 1 && $this->currentPage < $totalPages) {
                $navigationDefinitions = "children: [textbox-number-of-packages, textbox-pagination, button-tools-previous, button-tools-next];\n";
            } else {
                $navigationDefinitions = "children: [textbox-number-of-packages, textbox-pagination, button-tools-previous];\n";
            }

            $styles[] = "
            navigation {
                $navigationDefinitions
            }\n";
        }

        FileManager::writeFile(self::UPDATER_PAGINATION_THEME, implode("\n", $styles));
    }
}
