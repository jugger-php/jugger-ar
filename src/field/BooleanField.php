<?php

namespace jugger\ar\field;

class BooleanField extends BaseField
{
    protected function prepareValue($value)
    {
        return empty($value) ? false : true;
    }
}
