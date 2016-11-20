<?php

namespace jugger\ar\field;

use jugger\base\Configurator;

abstract class BaseField
{
    public $column;
	public $value;
	public $default;
    public $primary = false;
    public $unique = false;
    public $autoIncrement = false;

    protected $isSetDefault = false;

	public function __construct(array $config = [])
    {
        Configurator::setValues($this, $config);
        $this->isSetDefault = array_key_exists('default', $config);
	}

    protected function prepareValue()
    {
        return $this->value;
    }

	public function getValue()
    {
        $value = $this->prepareValue();
        if (!$value) {
            return $this->default;
        }
		return $value;
	}

	public function setValue($value)
    {
		$this->value = $value;
	}
}
