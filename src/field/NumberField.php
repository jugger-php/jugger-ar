<?php

namespace jugger\ar\field;

class NumberField extends BaseField
{
    /**
     * Масштаб
     * Число знаков в целой и дробной частях
     * @var integer
     */
    public $scale;
    /**
     * Число знаков после запятой
     * @var integer
     */
    public $accuracy;

    protected function prepareValue()
    {
        if (is_numeric($this->value)) {
            return (float) $this->value;
        }
        else {
            throw new ValueException($this);
        }
    }
}
