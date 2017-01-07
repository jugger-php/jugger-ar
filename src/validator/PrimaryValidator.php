<?php

namespace jugger\ar\validator;

use jugger\model\validator\BaseValidator;

/**
 * Fake validator
 */
class PrimaryValidator extends BaseValidator
{
    public function validate($value): bool
    {
        return true;
    }
}
