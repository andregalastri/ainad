<?php

namespace Core\Controllers;

use \Core\Classes\FileManager;
use \Core\Interfaces\CommonFiles;
use \Core\Interfaces\CommonDirectories;

class Updater implements CommonFiles, CommonDirectories
{
    use \Core\Traits\CommonMethods;

    const UPDATER_BASE_THEME = AINAD_BASE_DIR.'/rofi/widgets/updater/updater-base.rasi';
    const UPDATER_PAGINATION_THEME = AINAD_BASE_DIR.'/rofi/widgets/updater/updater-pagination.rasi';
    const UPDATER_PACKAGES_THEME = AINAD_BASE_DIR.'/rofi/widgets/updater/updater-packages.rasi';
    const UPDATER_ROFI_CMD = AINAD_BASE_DIR.'/rofi/widgets/updater/updater-rofi-cmd.bash';
    const APPLY_CMD = AINAD_BASE_DIR.'/rofi/widgets/updater/apply.bash';
    const PACKAGE_DATA = AINAD_BASE_DIR.'/php/files/updater/package-data.php';

    /**
     * ROFI seems to be too much slow if many packages are displayed, so, we
     * need to limit the maximum numbers of packages displayed per page.
     *
     * However, even if we could show more packages per page, we use custom
     * keyboard actions to let the user mark or unmark packages that will be
     * installed. The maximum number of custom keyboard actions allowed by ROFI
     * is 19, but three are already used:
     *
     * - One for the "Apply" button
     * - Two for the navigation buttons: Next page and previous page.
     *
     * So, the maximum number of packages per page we can use is 16. That
     * means that this constant cannot be bigger than 16.
     */
    const PACKAGES_PER_PAGE = 10;

    /**
     * Sets a minimum number of packages to notify the user that there are
     * updates to install.
     */
    const MIN_PACKAGES_TO_UPDATE = 0;

    /**
     * @var array $packageList          Stores the package list data. Each array
     *                                  key needs to store another array with
     *                                  the following associative keys:
     *
     *                                   - name: the package name.
     *                                   - oldVersion: the current version
     *                                   - newVersion: the available update
     *                                  version.
     *                                   - selected: if the package is marked to
     *                                  update.
     */
    private array $packageList = [];

    /**
     * @var int $currentPage            Stores the current page that is being
     *                                  displayed.
     */
    private int $currentPage = 1;

    public function __construct(){}
    
    /**
     * Executes the commands to check if there are new updates available and
     * updates the ROFI styles according to the results.
     *
     * @return bool
     */
    public function checkUpdates(): bool
    {
        /**
         * The Yay checks for AUR updates and the script 'checkupdates' checks
         * Pacman updates. The Yay check needs to be executed first because the
         * script 'checkupdates' cancels the execution if there are no new
         * updates.
         */
        exec('yay -Qua && checkupdates', $packageList);

        /**
         * If there are packages available, they are stored in the $packageList
         * array, which each index stores an string like this:
         *
         * [
         *     0 => 'google-chrome 106.0-1 -> 107.0-1',
         *     1 => 'firefox 106.0.4-1 -> 106.0.5-1',
         *     ...
         * ];
         *
         * We need to show these data on different columns, so we split the
         * string using the space as delimiter, storing each value in an proper
         * key.
         */
        foreach ($packageList as $package) {
            $package = explode(' ', $package);

            $this->packageList[] = [
                'name' => $package[0],
                'oldVersion' => $package[1],
                'newVersion' => $package[3],
                'selected' => true,
            ];
        }

        /**
         * If the $packageList property has packages, it proceeds to create the
         * ROFI styles to show the list of available updates. If not, it creates
         * styles that shows a message that the system is already updated.
         */
        $minPackages = self::MIN_PACKAGES_TO_UPDATE == 0 ? 1 : self::MIN_PACKAGES_TO_UPDATE;

        if (count($this->packageList) >= $minPackages) {
            $this->writePackageData();
            $this->polybarHook([0]);
            return true;
        } else {
            $this->writeUpToDateData();
            $this->polybarHook([1]);
            return false;
        }
    }

    /**
     * Launches the updater screen. When the $arg[0] is set and has the value
     * 'recheckUpdates', it reruns the checkUpdates() method.
     *
     * @param array $arg                Arguments from the command line.
     *
     * @index null|string $arg[0]       If the value is 'recheckUpdates', then
     *                                  the script will checks for new updates
     *                                  before launch the updater screen.
     *
     * @return void
     */
    public function launchUpdater(array $arg): void
    {
        /**
         * If the $arg[0] is set and its value is 'recheckUpdates', it shows an
         * notification that the updater is checking for new updates. The
         * notification remains open until the verification ends.
         */
        if (isset($arg[0]) and $arg[0] == 'recheckUpdates') {
            exec('dunstify -i "update-manager" -t 0 -r 1 -u "low" "Gerenciador de atualizações"  "Buscando por novas atualizações.\nPor favor aguarde..."');
            $this->checkUpdates();
            exec('dunstify -C 1');
        }

        exec('rofi -no-config -show updater -modi "updater:'.self::UPDATER_ROFI_CMD.'" -theme "'.self::UPDATER_BASE_THEME.'"');
    }
    
    /**
     * This method just checks if the 'hasUpdates' key from the package data
     * file is true. If it is, then it shows an icon in the Polybar panel,
     * notifying the user that there are new updates available.
     *
     * @return string
     */
    public function polybarIcon(): string
    {
        $packageData = require(self::PACKAGE_DATA);
        return $packageData['hasUpdates'] ? '%{T5}%{T-}' : '';
    }
    
    /**
     * This method runs the Polybar hook number zero of the check-updates
     * modules. This hook simples calls for the polybarIcon() method above. It
     * is used mainly by the loop shell script 1-hour, to check if the
     * checkUpdates() method found new updates every hour.
     *
     * @param array $arg                Arguments from the command line or
     *                                  methods.
     *
     * @index int $arg[0]               The ID of the hook that will be executed.
     * @return void
     */
    public function polybarHook(array $arg): void
    {
        $this->setPolybarPids();
        exec('polybar-msg -p '.$this->polybarData['main'].' action "#check-updates.hook.'.$arg[0].'"');
    }
    
    /**
     * Every package can be marked or unmarked by the user, to be updated or
     * not. When the user clicks in the checkbox, it gets the proper package
     * index and, if it is marked, turn it unmarked, and vice versa.
     *
     * @param array $arg                Arguments from the command line.
     *
     * @index int $arg[0]               The package position in the list, goind
     *                                  from 0 (first package) to 9 (last
     *                                  package).
     * @return void
     */
    public function setPackageSelected(array $arg): void
    {
        $this->getPackages();

        /**
         * Here we need to do some math.
         *
         * We use 10 custom keyboard bindings from ROFI per page, starting
         * from kb-custom-10 to kb-custom-19. This means that if we are on
         * page 1 an we click on the first checkbox, we are triggering the
         * kb-custom-10 action. This action will send send the value 10 to the
         * updater-rofi-cmd.bash, via ROFI_RETV variable. The number 10 in the
         * script represents the package number 0, so the bash script send the
         * value 0 to this method here, via $arg[0].
         *
         * So, $packageList index that we need to mark or unmark is the package
         * in the index number 0, like this:
         *
         * $this->packageList[0]['selected'];
         *
         * So, an calculation is made. We get the maximum number of packages per
         * page and multiply by the current page.
         *
         * This means: 10 * 1 = 10
         *
         * This result need to be subtracted by the maximum number of packages
         * per page plus the position received by the $arg[0].
         *
         * This means: 10 - 10 + 0 = 0
         *
         * And them we have the index of the $packageList that will be updated.
         *
         * BUT WHY ALL THIS WORK?
         *
         * Simple, because use can have multiple pages, but the kb-custom keys
         * are always the same. They will always send $arg[0] from 0 to 9, even
         * in the page 2 (instead of sending 10 to 19).
         *
         * Lets redo the calculation, imagining that we are clicking in the
         * third package of the list (which sends the value 2 to the $arg[0]) of
         * the page 2:
         *
         * This means: (10 * 2) - 10 + 2 = 12
         *
         * This will affect the package positioned in the index of 12 (in other
         * words, the third package in the second page).
         *
         * $this->packageList[12]['selected'];
         */
        $selected = (self::PACKAGES_PER_PAGE * $this->currentPage) - self::PACKAGES_PER_PAGE + $arg[0];

        $this->packageList[$selected]['selected'] = !$this->packageList[$selected]['selected'];
        $this->writePackageData();
    }
    
    /**
     * Updates the $currentPage property, adding or subtracting its value to
     * show packages of each page.
     *
     * @param array $arg                Arguments from the command line.
     *
     * @index string $arg[0]            When 'next' adds one to the
     *                                  $currentPage. If not, subtracts.
     *
     * @return void
     */
    public function setPage(array $arg): void
    {
        $this->getPackages();
        
        if (trim($arg[0]) == 'next') {
            $this->currentPage++;
        } else {
            $this->currentPage--;
        }

        $this->writePackageData();
    }
    
    /**
     * Executes the installation of the marked packages from the update list.
     *
     * @return void
     */
    public function applyUpdates(): void
    {
        $this->getPackages();

        $numberOfSelectedPackages = 0;

        $selectedPackages = [];

        /**
         * Store in an array the names of the packages that were marked to be
         * installed. It also increments the $numberOfSelectedPackages variable.
         */
        foreach ($this->packageList as $key => $packageData) {
            if ($packageData['selected']) {
                $numberOfSelectedPackages++;
                $selectedPackages[$key] = $packageData['name'];
            }
        }

        /**
         * Checks if the $numberOfSelectedPackages variable is bigger than zero,
         * because the update process cannot proceed if there are zero selected
         * updates.
         */
        if ($numberOfSelectedPackages > 0) {

            /**
             * Close the window and runs the Polybar hook 1 of the check-updates
             * module to remove the update icon from the panel.
             */
            exec('killall -9 rofi');
            $this->polybarHook([1]);
            
            /**
             * For now, the update needs to be executed in a terminal window.
             * This command pass the selected packages to the bash script that
             * runs Yay to apply the updates.
             */
            $updateStatus = exec('xfce4-terminal -e "'.self::APPLY_CMD.' \"'.implode(' ', $selectedPackages).'\""');

            if ($updateStatus == 0) {
                $this->writeUpToDateData();
            }
        } else {
            exec('dunstify -i "dialog-warning" -u "critical" -t 10000 "Atenção!" "É necessário selecionar ao menos um pacote de atualização."');
        }
    }
    
    /**
     * Creates the PHP file storing the package list of the available updates
     * and other data as well.
     *
     * It also calls for the other methods that creates another files: the ROFI
     * styles of the package list and the pagination.
     *
     * @return void
     */
    private function writePackageData(): void
    {
        FileManager::writePhpVar(self::PACKAGE_DATA, [
            'hasUpdates' => true,
            'list' => $this->packageList,
            'currentPage' => $this->currentPage,
        ]);

        $this->createPackageList();
        $this->createPagination();
    }
    
    /**
     * Creates the file with ROFI styles to display the inforamtion to the user.
     *
     * @return void
     */
    private function createPackageList(): void
    {
        /**
         * For each package in the $packageList property, it will defines the
         * styles.
         */
        for ($i = 0, $actionNumber = 1; $i < count($this->packageList); $i++, $actionNumber++) {

            $package = $this->packageList[$i];
            
            /**
             * By default, the checkbox is not marked. However, if the selected
             * key of the package is true, then it is replaced by a marked
             * checkbox.
             */
            $checkbox = self::ASSETS_DIR.'/square.svg';
            if ($package['selected']) {
                $checkbox = self::ASSETS_DIR.'/square-check-solid.svg';
            }

            /**
             * The styles for the package.
             */
            $contentFile[] = "
            icon-status-$i {
                expand: false;
                filename: \"$checkbox\";
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

            /**
             * The $actionNumber is simply the kb-custom number. It cannot be
             * bigger or equal to the maximum numbers of packages per page.
             */
            if ($actionNumber >= self::PACKAGES_PER_PAGE) {
                $actionNumber = 0;
            }
        }

        /**
         * Saves the styles.
         */
        FileManager::writeFile(self::UPDATER_PACKAGES_THEME, implode("\n", $contentFile));
    }
    
    /**
     * Creates the file with ROFI styles to display the pagination and the
     * button to navigates between them.
     *
     * @return void
     */
    private function createPagination(): void
    {
        $totalPackages = count($this->packageList);
        $totalPages = (int)ceil($totalPackages / self::PACKAGES_PER_PAGE);
        $pageEnd = $this->currentPage * self::PACKAGES_PER_PAGE;
        $pageStart = $pageEnd - self::PACKAGES_PER_PAGE;

        /**
         * This array stores the lines that will be displayed. It is initialized
         * with the headers and for each package it adds a line to it. At the
         * end, this array will be converted to a string.
         */
        $linesToShow[] = 'line-label';

        /**
         * Creates the package list that will be shown in the current page.
         */
        for ($i = $pageStart; $i < $pageEnd; $i++) {
            if ($i >= $totalPackages) {
                break;
            }

            $linesToShow[] = 'line-'.$i;
        }

        /**
         * Creates the styles with the packages that are part of the current
         * page.
         */
        $styles[] = "
        update-list {
            children: [".implode(',', $linesToShow)."];
        }\n";


        /**
         * Creates the information about how many updates are selected. This
         * information is placed near the number of all packages available.
         */
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

        /**
         * Finally, it checks. If the total pages is bigger than one, then the
         * navigation buttons of the pagination needs to be shown. If not, these
         * buttons are not displayed.
         */
        if ($totalPages > 1) {

            /**
             * If the current page is the first, then the only button showed is
             * the 'Next' button.
             */
            if ($this->currentPage == 1) {
                $navigationDefinitions = "children: [textbox-number-of-packages, textbox-pagination, button-tools-next];\n";

            /**
             * If the current page is not the first nor the last, then both
             * buttons are showed: the 'Next' and the 'Previous' buttons.
             */
            } elseif ($this->currentPage > 1 && $this->currentPage < $totalPages) {
                $navigationDefinitions = "children: [textbox-number-of-packages, textbox-pagination, button-tools-previous, button-tools-next];\n";

            /**
             * If the current page is the last, then the only button showed is
             * the 'Previous' button.
             */
            } else {
                $navigationDefinitions = "children: [textbox-number-of-packages, textbox-pagination, button-tools-previous];\n";
            }

            /**
             * Adds the navigation to the styles data.
             */
            $styles[] = "
            navigation {
                $navigationDefinitions
            }\n";
        }

        /**
         * Saves the styles.
         */
        FileManager::writeFile(self::UPDATER_PAGINATION_THEME, implode("\n", $styles));
    }

    /**
     * Updates the PHP file and the ROFI styles to display the screen that
     * everything is up to date.
     *
     * @return void
     */
    private function writeUpToDateData(): void
    {
        /**
         * Saves the PHP file with empty data (because all is up to date and
         * there is no updates available).
         */
        FileManager::writePhpVar(self::PACKAGE_DATA, [
            'hasUpdates' => false,
            'list' => [],
            'currentPage' => 1,
        ]);

        /**
         * Saves the ROFI styles with a message informing that the system is up
         * to date.
         */
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
     * Imports the package data from the PHP file (only if the $packageList is
     * empty) and initiates the properties with the values imported.
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
}
