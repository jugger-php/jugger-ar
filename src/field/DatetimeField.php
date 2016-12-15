<?php

namespace jugger\ar\field;

class DatetimeField extends BaseField
{
    const FORMAT_TIMESTAMP = 'timestamp';

    protected $format = 'Y-m-d H:i:s';

    public function __construct(array $config)
    {
        if (isset($config['format'])) {
            $this->format = $config['format'];
            unset($config['format']);
        }

        parent::__construct($config);
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function getValue()
    {
        if (is_null($this->value)) {
            return $this->default;
        }
        elseif (!($this->value instanceof \DateTime)) {
            return null;
        }
        elseif ($this->format === self::FORMAT_TIMESTAMP) {
            return $this->value->getTimestamp();
        }
        else {
            return $this->value->format($this->format);
        }
    }

    protected function prepareValue($input)
    {
        if (is_integer($input) || is_float($input)) {
            $value = new \DateTime();
            $value->setTimestamp((int) $input);
        }
        elseif (is_string($input)) {
            $value = \DateTime::createFromFormat($this->format, $input);
            if ($value === false) {
                $value = null;
            }
        }
        else {
            $value = null;
        }
        return $value;
    }
}
