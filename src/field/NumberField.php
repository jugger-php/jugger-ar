<?php

namespace jugger\ar\field;

class NumberField extends BaseField
{
    protected function prepareValue($value)
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        else {
            return null;
        }
    }
}
