<?php

namespace jugger\ar\field;

class DatetimeField extends BaseField
{
    public $format = 'Y-m-d H:i:s';

    protected function prepareValue()
    {
        if ($this->format === 'timestamp') {
            return $this->value->getTimestamp();
        }
        else {
            return $this->value->format($this->format);
        }
    }

    public function setValue($value)
    {
        if (is_integer($value)) {
            $this->value = new \DateTime();
            $this->value->setTimestamp($value);
        }
        else {
            $this->value = \DateTime::createFromFormat($this->format, $value);
        }
    }
}
