<?php

namespace jugger\ar\field;

class IntegerField extends BaseField
{
	protected function prepareValue($value)
    {
        if (is_numeric($value)) {
            return (int) $value;
        }
        else {
            return null;
        }
    }
}
