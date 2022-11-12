<?php

namespace Core\Classes;

/**
 * This method mixes both classes: DateTime and IntlDateFormatter.
 */
class Datetime
{
    /**
     * @var \DateTime $datetime         Stores the datetime object.
     */
    private \DateTime $datetime;

    /**
     * @var \IntlDateFormatter          Stores the object that will format the
     *                                  datetime based on location and language.
     */
    private \IntlDateFormatter $formatter;
    
    /**
     * Initiates the properties
     *
     * @param  null|string $dateString  The Date/time string that will be
     *                                  worked. When null, uses the current
     *                                  date.
     *
     * @param  string $locale           The language that will define timezones
     *                                  and other stuff. Default is 'en_US'.
     *
     * @return void
     */
    public function __construct(?string $dateString = null, string $locale = 'en_US')
    {
        $this->datetime = new \DateTime($dateString);
        $this->formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT);

    }
    
    /**
     * Prints the date/time using the given pattern. This method takes in to
     * account the defined language and timezone.
     *
     * @param  mixed $pattern           Defines how the data that will be
     *                                  displayed.
     *
     * @return string
     */
    public function format(string $pattern): string
    {
        $this->formatter->setPattern($pattern);
        return $this->formatter->format($this->datetime);
    }
    
    /**
     * Modify the date/time using the given pattern.
     *
     * @param  mixed $pattern           Defines which modification will be made.
     *
     * @return void
     */
    public function modify(string $pattern): void
    {
        $this->datetime->modify($pattern);
    }
}
