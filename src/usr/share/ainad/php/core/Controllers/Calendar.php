<?php

namespace Core\Controllers;

use \Core\Classes\Datetime;
use \Core\Classes\FileManager;

/**
 * Contains the methods and properties related to the Polybar Taskbar module.
 */
class Calendar
{
    const CALENDAR_BASE_THEME = AINAD_BASE_DIR.'/rofi/widgets/calendar/calendar-base.rasi';
    const CALENDAR_DYNAMIC_THEME = AINAD_BASE_DIR.'/rofi/widgets/calendar/calendar-dynamic.rasi';

    private array $styles;
    private string $locale;

    private Datetime $weekdayNames;
    private Datetime $currentDate;
    private Datetime $argDate;
    private Datetime $lastArgDay;
    
    /**
     * __construct
     *
     * @param  mixed $client
     * @return void
     */
    public function __construct()
    {
        date_default_timezone_set(explode('=', exec('timedatectl show | grep "Timezone"'))[1]);
        $locale = explode('.', explode('=', exec('locale | grep "LANG"'))[1])[0];

        $this->weekdayNames = new Datetime('2022-05-01', $locale);
        
        $this->currentDate = new Datetime(date('Y-m-d'), $locale);

        $this->argDate = new Datetime(date('Y-m').'-01', $locale);

        $this->lastArgDay = new Datetime(date('Y-m').'-01', $locale);
        $this->lastArgDay->modify('+1 month -1 day');
    }
    
    /**
     * buildCalendarGrid
     *
     * @param  mixed $arg
     * @return void
     */
    private function buildCalendarGrid(): void
    {
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

        $this->styles[] = "
        textbox-today-day {
            expand: false;
            font: \"RobotoCondensed 25\";
            text-color: #8A94B1;
            content: \"".$this->currentDate->format('dd')."\";
            margin: -7px 0 0 0;
        }\n";

        $this->styles[] = "
        textbox-today-weekday {
            font: \"RobotoCondensed 10\";
            text-color: #8A94B1;
            content: \"".mb_strtoupper($this->currentDate->format('EEEE'), 'UTF-8')."\";
        }\n";

        $baseCell = function(int|string $dayNumber): string
        {
            return "vertical-align: 0.5;
                horizontal-align: 0.5;
                border-radius: 3px;
                padding: 4px;
                content: \"".$dayNumber."\";";
        };

        $commonCell = function(int $cellCount, int|string $dayNumber) use ($baseCell): void
        {
            $this->styles[] = "
            textbox-cell-".$cellCount." {
                ".$baseCell($dayNumber)."
                text-color: #8A94B1;
                background-color: transparent;
                font: \"RobotoCondensed 11\";
            }\n";
        };

        $todayCell = function(int $cellCount, int|string $dayNumber) use ($baseCell): void
        {
            $this->styles[] = "
            textbox-cell-".$cellCount." {
                ".$baseCell($dayNumber)."
                text-color: #FFFFFF;
                background-color: #CF4D80;
                font: \"RobotoCondensed Bold 11\";
            }\n";
        };

        $sundayCell = function(int $cellCount, int|string $dayNumber) use ($baseCell): void
        {
            $this->styles[] = "
            textbox-cell-".$cellCount." {
                ".$baseCell($dayNumber)."
                text-color: #CF4D80;
                background-color: #2E323D;
                font: \"RobotoCondensed Bold 11\";
            }\n";
        };

        $saturdayCell = function(int $cellCount, int|string $dayNumber) use ($baseCell): void
        {
            $this->styles[] = "
            textbox-cell-".$cellCount." {
                ".$baseCell($dayNumber)."
                text-color: #8A94B1;
                background-color: #2E323D;
                font: \"RobotoCondensed Bold 11\";
            }\n";
        };

        $dayNumber = 1;
        $weekdayNumber = 0;

        for ($i = 1, $cellCount = 7; $i <= 42 ; $i++, $cellCount++) { 
            if ($i < $this->argDate->format('e') or $dayNumber > $this->lastArgDay->format('d')) {
                $commonCell($cellCount, '');
            } else {
                if ($this->argDate->format('Y-M').'-'.$dayNumber == $this->currentDate->format('Y-M-d')) {
                    $todayCell($cellCount, $dayNumber);
                } elseif ($weekdayNumber == 6) {
                    $saturdayCell($cellCount, $dayNumber);
                } elseif ($weekdayNumber == 0) {
                    $sundayCell($cellCount, $dayNumber);
                } else {
                    $commonCell($cellCount, $dayNumber);
                }

                $dayNumber++;
            }

            if ($weekdayNumber >= 6) {
                $weekdayNumber = 0;
            } else {
                $weekdayNumber++;
            }
        }

        FileManager::writeFile(self::CALENDAR_DYNAMIC_THEME, implode($this->styles));
    }
    
    /**
     * openCalendar
     *
     * @return void
     */
    public function openCalendar(): void
    {
        $option = [
            'previousYear' => '',
            'previousMonth' => '',
            0 => '',
            1 => '',
            2 => '',
            'nextMonth' => '',
            'nextYear' => '',
        ];

        $choice = '';

        while (true) {
            $this->buildCalendarGrid();

            $choice = exec('echo -en "'.implode("\n", $option).'" | rofi -dmenu -p "'.ucfirst($this->argDate->format('MMMM/YYYY')).'" -select "'.$choice.'" -theme "'.self::CALENDAR_BASE_THEME.'"');

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

            $newArgDate = $this->argDate->format('Y-M').'-01';

            $this->argDate->modify($newArgDate.' '.$modifier);
            $this->lastArgDay->modify($newArgDate.' '.$modifier);
            $this->lastArgDay->modify('+1 month -1 day');
        }
    }
}
