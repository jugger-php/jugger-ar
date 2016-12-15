<?php

namespace jugger\ar\field;

use jugger\base\Configurator;

abstract class BaseField
{
	public $default;
    public $primary = false;
    public $unique = false;
    public $autoIncrement = false;

    protected $column;
	protected $value;

    abstract protected function prepareValue($value);

	public function __construct(array $config)
    {
		if (isset($config['column'])) {
			$this->column = $config['column'];
			unset($config['column']);
		}
		else {
			throw new \Exception("Property 'column' is require");
		}

		if (isset($config['value'])) {
			$this->setValue($config['value']);
			unset($config['value']);
		}

        Configurator::setValues($this, $config);
	}

	public function getColumn()
	{
		return $this->column;
	}

	public function getValue()
    {
        if (is_null($this->value)) {
            return $this->default;
        }
		return $this->value;
	}

	public function setValue($value)
    {
		$this->value = $this->prepareValue($value);
	}
}
