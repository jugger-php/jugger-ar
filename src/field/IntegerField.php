<?php

namespace jugger\ar\field;

class IntegerField extends BaseField
{
	protected function prepareValue()
    {
        if (is_numeric($this->value)) {
            return (int) $this->value;
        }
        else {
            return null;
        }
    }
}
