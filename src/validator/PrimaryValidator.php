<?php

namespace jugger\ar\validator;

use jugger\model\validator\BaseValidator;

class PrimaryValidator extends BaseValidator
{
    /**
     * Возвращает всегда TRUE, чтобы можно было использовать поле как AUTO_INCREMENT:
     * При значении NULL - значение автоматически подставляется самой базой
     */
    public function validate($value): bool
    {
        return true;
    }
}
