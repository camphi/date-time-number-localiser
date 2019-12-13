<?php

class Localiser
{

    public function localized_date_filter($date, $dateFormat = 'medium', $timeFormat = 'medium', $locale = null, $format = null, $calendar = 'gregorian')
    {
        $formatValues = [
            'none' => \IntlDateFormatter::NONE,
            'short' => \IntlDateFormatter::SHORT,
            'medium' => \IntlDateFormatter::MEDIUM,
            'long' => \IntlDateFormatter::LONG,
            'full' => \IntlDateFormatter::FULL,
        ];
        if (!class_exists('IntlTimeZone')) {
            $formatter = \IntlDateFormatter::create(
                $locale,
                $formatValues[$dateFormat],
                $formatValues[$timeFormat],
                $date->getTimezone()->getName(),
                'gregorian' === $calendar ? \IntlDateFormatter::GREGORIAN : \IntlDateFormatter::TRADITIONAL,
                $format
            );
            return $formatter->format($date->getTimestamp());
        }
        $formatter = \IntlDateFormatter::create(
            $locale,
            $formatValues[$dateFormat],
            $formatValues[$timeFormat],
            \IntlTimeZone::createTimeZone($date->getTimezone()->getName()),
            'gregorian' === $calendar ? \IntlDateFormatter::GREGORIAN : \IntlDateFormatter::TRADITIONAL,
            $format
        );
        return $formatter->format($date->getTimestamp());
    }
    
    public function localized_number_filter($number, $style = 'decimal', $type = 'default', $locale = null)
    {
        static $typeValues = [
            'default' => \NumberFormatter::TYPE_DEFAULT,
            'int32' => \NumberFormatter::TYPE_INT32,
            'int64' => \NumberFormatter::TYPE_INT64,
            'double' => \NumberFormatter::TYPE_DOUBLE,
            'currency' => \NumberFormatter::TYPE_CURRENCY,
        ];
        $formatter = get_number_formatter($locale, $style);
        if (!isset($typeValues[$type])) {
            throw new SyntaxError(sprintf('The type "%s" does not exist. Known types are: "%s"', $type, implode('", "', array_keys($typeValues))));
        }
        return $formatter->format($number, $typeValues[$type]);
    }
    
    public function localized_currency_filter($number, $currency = null, $locale = null)
    {
        $formatter = get_number_formatter($locale, 'currency');
        return $formatter->formatCurrency($number, $currency);
    }
    
    /**
     * Gets a number formatter instance according to given locale and formatter.
     *
     * @param string $locale Locale in which the number would be formatted
     * @param string $style  Style of the formatting
     *
     * @return \NumberFormatter A NumberFormatter instance
     */
    public function get_number_formatter($locale, $style)
    {
        static $formatter, $currentStyle;
        $locale = null !== $locale ? $locale : \Locale::getDefault();
        if ($formatter && $formatter->getLocale() === $locale && $currentStyle === $style) {
            // Return same instance of NumberFormatter if parameters are the same
            // to those in previous call
            return $formatter;
        }
        static $styleValues = [
            'decimal' => \NumberFormatter::DECIMAL,
            'currency' => \NumberFormatter::CURRENCY,
            'percent' => \NumberFormatter::PERCENT,
            'scientific' => \NumberFormatter::SCIENTIFIC,
            'spellout' => \NumberFormatter::SPELLOUT,
            'ordinal' => \NumberFormatter::ORDINAL,
            'duration' => \NumberFormatter::DURATION,
        ];
        if (!isset($styleValues[$style])) {
            throw new SyntaxError(sprintf('The style "%s" does not exist. Known styles are: "%s"', $style, implode('", "', array_keys($styleValues))));
        }
        $currentStyle = $style;
        return \NumberFormatter::create($locale, $styleValues[$style]);
    }
}
