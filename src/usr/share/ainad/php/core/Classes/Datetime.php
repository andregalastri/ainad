<?php

namespace Core\Classes;

/**
 * Contains the methods and properties related to the Polybar Taskbar module.
 */
class Datetime
{
    private \DateTime $datetime;
    private \IntlDateFormatter $formatter;

    /**
     * __construct
     *
     * @param  mixed $client
     * @return void
     */
    public function __construct(?string $dateString = null, string $locale = 'en_US')
    {
        $this->datetime = new \DateTime($dateString);
        $this->formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT);

    }
    
    /**
     * format
     *
     * @param  mixed $pattern
     * @return string
     */
    public function format(string $pattern): string
    {
        $this->formatter->setPattern($pattern);
        return $this->formatter->format($this->datetime);
    }
    
    /**
     * modify
     *
     * @param  mixed $pattern
     * @return void
     */
    public function modify(string $pattern): void
    {
        $this->datetime->modify($pattern);
    }
}
