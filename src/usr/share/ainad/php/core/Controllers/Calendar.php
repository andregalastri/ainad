<?php

namespace Core\Controllers;

use \Core\Classes\Datetime;
use \Core\Classes\FileManager;
use \Core\Interfaces\CommonFiles;

class Calendar implements CommonFiles
{
    const CALENDAR_BASE_THEME = AINAD_BASE_DIR.'/rofi/widgets/calendar/calendar-base.rasi';
    const CALENDAR_DYNAMIC_THEME = AINAD_BASE_DIR.'/rofi/widgets/calendar/calendar-dynamic.rasi';

    /**
     * @var array $styles               The dynamic data of the calendar.
     */
    private array $styles;

    /**
     * @var Datetime $weekdayNames      The date that will be used to print the
     *                                  weekday names, based on the current
     *                                  locale of the system.
     */
    private Datetime $weekdayNames;

    /**
     * @var Datetime $currentDate       The current date.
     */
    private Datetime $currentDate;

    /**
     * @var Datetime $argDate           The date that comes from the user, when
     *                                  it navigates through the months and
     *                                  years.
     */
    private Datetime $argDate;

    /**
     * @var Datetime $lastArgDay        Helps to detect the last day of the
     *                                  months.
     */
    private Datetime $lastArgDay;
    

    /* Methods */

    /**
     * Defines the timezone and Datetime properties.
     *
     * @return void
     */
    public function __construct()
    {
        /**
         * Importing the system data from the file. This data is created on the
         * startup of the system (autostart-ainad.bash file) and store things
         * like timezone and locale.
         */
        $systemData = require(self::SYSTEM_DATA);

        date_default_timezone_set($systemData['timezone']);

        $locale = $systemData['locale'];

        /**
         * Defines a month that has its first day as Sunday. This date will be
         * incremented and stored until reaches Saturday. The purpose of this is
         * to easily get the weekday names based on the locale of the system.
         */
        $this->weekdayNames = new Datetime('2022-05-01', $locale);
        
        $this->currentDate = new Datetime(date('Y-m-d'), $locale);

        /**
         * Initiates the argDate property starting on day one of the current
         * month and year. This date will be incremented until it reaches the
         * last day of the month.
         */
        $this->argDate = new Datetime(date('Y-m').'-01', $locale);

        /**
         * An easy way to define the last day of the month is to set the day one
         * of the current month and year, increment one month and then decrement
         * one day.
         */
        $this->lastArgDay = new Datetime(date('Y-m').'-01', $locale);
        $this->lastArgDay->modify('+1 month -1 day');
    }
    
    /**
     * Defines the menu to navigate through the calendar, calls the the method
     * to build the theme that will be used by ROFI to display the calendar.
     *
     * @return void
     */
    public function openCalendar(): void
    {
        /**
         * Navigation options.
         */
        $option = [
            'previousYear' => '',
            'previousMonth' => '',
            0 => '',
            1 => '',
            2 => '',
            'nextMonth' => '',
            'nextYear' => '',
        ];

        /**
         * Initiates the $choice variable as empty before the loop starts. The
         * $choice variable keeps the last selected button the user chose, so it
         * can navigates the calendar more easily.
         */
        $choice = '';

        /**
         * The calendar is an infinite loop. The only way to stop it is to
         * choose an undefined $choice value.
         */
        while (true) {
            /**
             * Builds the calendar theme.
             */
            $this->buildCalendarGrid();

            /**
             * Calls ROFI and waits for an choice.
             */
            $choice = exec('echo -en "'.implode("\n", $option).'" | rofi -dmenu -p "'.ucfirst($this->argDate->format('MMMM/YYYY')).'" -select "'.$choice.'" -theme "'.self::CALENDAR_BASE_THEME.'"');

            /**
             * If the $choice is equal to a valid button, then it sets a proper
             * $modifier. If not, it simply ends the script.
             */
            switch ($choice) {
                case $option['previousYear']:
                    $modifier = '-1 Year';
                break;

                case $option['previousMonth']:
                    $modifier = '-1 Month';
                break;

                case $option['nextMonth']:
                    $modifier = '+1 Month';
                break;

                case $option['nextYear']:
                    $modifier = '+1 Year';
                break;

                default:
                    exit;
            }

            /**
             * If the user chooses a valid $choice, then the script will
             * continue here, and it means that the user chose an new $argDate.
             * Because of that, we need to store the current $argDate and then
             * modify it to the $argDate that the user selected.
             */
            $currentArgDate = $this->argDate->format('Y-M').'-01';

            $this->argDate->modify($currentArgDate.' '.$modifier);
            $this->lastArgDay->modify($currentArgDate.' '.$modifier);
            $this->lastArgDay->modify('+1 month -1 day');
        }
    }

    /**
     * Creates the .rasi file that has the calendar data that will be displayed
     * to the user.
     *
     * Each ROFI property os stored in the $styles array, which will be imploded
     * to create an string that will be stored in a file.
     *
     * @return void
     */
    private function buildCalendarGrid(): void
    {
        /**
         * This first loop creted the weekday names that will be placed as the
         * header of the columns of the grid, starting on Sunday and ending on
         * Saturday
         */
        for ($i = 0; $i <= 6; $i++) {
            $this->styles[] = "
            textbox-cell-".$i." {
                vertical-align: 0.5;
                horizontal-align: 0.5;
                font: \"RobotoCondensed Bold 10\";
                padding: 4px;
                text-color: #8A94B1;
                background-color: #2E323D;
                content: \"".mb_strtoupper(rtrim($this->weekdayNames->format('E'), '.'), 'UTF-8')."\";
            }\n";

            $this->weekdayNames->modify('+1 day');
        }

        /**
         * Stores the number of the today's day.
         */
        $this->styles[] = "
        textbox-today-day {
            expand: false;
            font: \"RobotoCondensed 25\";
            text-color: #8A94B1;
            content: \"".$this->currentDate->format('dd')."\";
            margin: -7px 0 0 0;
        }\n";

        /**
         * Stores the full name of the today's weekday.
         */
        $this->styles[] = "
        textbox-today-weekday {
            font: \"RobotoCondensed 10\";
            text-color: #8A94B1;
            content: \"".mb_strtoupper($this->currentDate->format('EEEE'), 'UTF-8')."\";
        }\n";

        /**
         * The calendar has 7 columns and 6 lines, creating a grid of 42 cells.
         * Each cell has a base style that is defined by this closure function.
         *
         * @param string dayNumber      The day number that will be the content
         *                              of the cell.
         * 
         * @return string
         */
        $baseCell = function (string $dayNumber): string {
            return "vertical-align: 0.5;
                horizontal-align: 0.5;
                border-radius: 3px;
                padding: 4px;
                content: \"".$dayNumber."\";";
        };

        /**
         * This is the common cell. It represents the common days of the
         * calendar.
         *
         * @param int $cellId           The ID of the cell, one of the 42
         *                              possible numbers of the grid.
         *
         * @param string dayNumber      The day number that will be the content
         *                              of the cell.
         * 
         * @use Closure $baseCell       The closure that will print the base
         *                              cell style.
         *
         * @return void
         */
        $commonCell = function (int $cellId, string $dayNumber) use (/*Closure*/ $baseCell): void {
            $this->styles[] = "
            textbox-cell-".$cellId." {
                ".$baseCell($dayNumber)."
                text-color: #8A94B1;
                background-color: transparent;
                font: \"RobotoCondensed 11\";
            }\n";
        };

        /**
         * This cell has a different style that highlights the today's day.
         * 
         * @param int $cellId           The ID of the cell, one of the 42
         *                              possible numbers of the grid.
         *
         * @param string dayNumber      The day number that will be the content
         *                              of the cell.
         * 
         * @use Closure $baseCell       The closure that will print the base
         *                              cell style.
         *
         * @return void
         */
        $todayCell = function (int $cellId, string $dayNumber) use (/*Closure*/ $baseCell): void {
            $this->styles[] = "
            textbox-cell-".$cellId." {
                ".$baseCell($dayNumber)."
                text-color: #FFFFFF;
                background-color: #CF4D80;
                font: \"RobotoCondensed Bold 11\";
            }\n";
        };

        /**
         * This is a special cell because it represents the days of Sunday. That
         * is why it has a different style than the common days.
         *
         * @param int $cellId           The ID of the cell, one of the 42
         *                              possible numbers of the grid.
         *
         * @param string dayNumber      The day number that will be the content
         *                              of the cell.
         * 
         * @use Closure $baseCell       The closure that will print the base
         *                              cell style.
         *
         * @return void
         */
        $sundayCell = function (int $cellId, string $dayNumber) use (/*Closure*/ $baseCell): void {
            $this->styles[] = "
            textbox-cell-".$cellId." {
                ".$baseCell($dayNumber)."
                text-color: #CF4D80;
                background-color: #2E323D;
                font: \"RobotoCondensed Bold 11\";
            }\n";
        };

        /**
         * This is a special cell because it represents the days of Saturday.
         * That is why it has a different style than the common days.
         *
         * @param int $cellId           The ID of the cell, one of the 42
         *                              possible numbers of the grid.
         *
         * @param string dayNumber      The day number that will be the content
         *                              of the cell.
         * 
         * @use Closure $baseCell       The closure that will print the base
         *                              cell style.
         *
         * @return void
         */
        $saturdayCell = function (int $cellId, string $dayNumber) use (/*Closure*/ $baseCell): void {
            $this->styles[] = "
            textbox-cell-".$cellId." {
                ".$baseCell($dayNumber)."
                text-color: #8A94B1;
                background-color: #2E323D;
                font: \"RobotoCondensed Bold 11\";
            }\n";
        };

        /**
         * Defines the number of the first day of the month.
         */
        $dayNumber = 1;

        /**
         * Defines the column of the grid.
         */
        $gridColumn = 1;

        /**
         * This loop will count from 1 to 42, which is the number of cells that
         * the calendar has.
         *
         * It also sets and increments the cell ID, starting from 7, because the
         * IDs from 0 to 6 were used in the the weekday names line.
         */
        for ($i = 1, $cellId = 7; $i <= 42 ; $i++, $cellId++) { 

            /**
             * If the argDate number is smaller than the cell count or the
             * $dayNumber is bigger than the last day of the month, then the
             * cell will be printed as empty, because they are not part of the
             * current calendar.
             */
            if ($i < $this->argDate->format('e') or $dayNumber > $this->lastArgDay->format('d')) {
                $commonCell($cellId, '');
            } else {
                /**
                 * However, if the cell is part of the calendar, then it checks:
                 *
                 * If the current $dayNumber is equal to the today's day, then
                 * the cell will have the style of the $todayCell.
                 *
                 * If not, if the $gridColumn is equal to 7 (which represents
                 * the Saturday column), then the cell will have the style of
                 * the $saturdayCell.
                 *
                 * If not, if the $gridColumn is equal to 1 (which represents
                 * the Sunday column), then the cell will have the style of the
                 * $sundayCell.
                 *
                 * If any of the above tests are not valid, then the cell will
                 * have the style of the $commonCell.
                 */
                if ($this->argDate->format('Y-M').'-'.$dayNumber == $this->currentDate->format('Y-M-d')) {
                    $todayCell($cellId, $dayNumber);
                } elseif ($gridColumn == 7) {
                    $saturdayCell($cellId, $dayNumber);
                } elseif ($gridColumn == 1) {
                    $sundayCell($cellId, $dayNumber);
                } else {
                    $commonCell($cellId, $dayNumber);
                }

                /**
                 * Also, as the cell is part of the calendar, then the
                 * $dayNumber needs to be incremented on each iteration.
                 */
                $dayNumber++;
            }

            /**
             * On each count of the cell, we need to check which column we are
             * in. There are only 7 columns, so, here we test:
             *
             * If the column is equal or bigger than 7, then we need to restart
             * the column to number 1. If not, then it just increments the
             * current number by one, until it reaches 7 to be restarted.
             */
            if ($gridColumn >= 7) {
                $gridColumn = 1;
            } else {
                $gridColumn++;
            }
        }

        /**
         * Saves the styles in the file theme.
         */
        FileManager::writeFile(self::CALENDAR_DYNAMIC_THEME, implode($this->styles));
    }
}
