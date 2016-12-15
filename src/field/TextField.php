<?php

namespace jugger\ar\field;

class TextField extends BaseField
{
    protected $length = 0;

    public function __construct(array $config)
    {
        if (isset($config['length'])) {
            $this->length = (int) $config['length'];
            unset($config['length']);
        }
        parent::__construct($config);
    }

    public function getLength()
    {
        return $length;
    }

    protected function prepareValue($value)
    {
        if ($this->length > 0 && strlen($value) > $this->length) {
            throw new \Exception("Столбец '{$this->column}': длинна строки больше указанного лимита {$this->length}");
        }
        elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        elseif (is_scalar($value)) {
            return (string) $value;
        }
        else {
            return null;
        }
    }
}
