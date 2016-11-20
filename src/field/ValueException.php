<?php

namespace jugger\ar\field;

class ValueException extends \Exception
{
    public function __construct(BaseField $field)
    {
        parent::__construct("Invalide value '{$field->value}' of column '{$field->column}'");
    }
}
