<?php

namespace jugger\ar;

trait ActiveRecordTrait
{
    public function __isset($name)
    {
		return array_key_exists($name, $this->fields);
	}

	public function __get($name)
    {
		if (array_key_exists($name, $this->fields)) {
			return $this->fields[$name]->getValue();
		}
        elseif (array_key_exists($name, $this->relations)) {
            $relation = $this->relations[$name];
            $selfField = $relation->getSelfField();
            $selfValue = $this->$selfField;

            return $relation->getValue($selfValue);
		}
		else {
			$class = get_called_class();
			throw new \ErrorException("Field or relation '{$name}' not found in '{$class}'");
		}
	}

	public function __set($name, $value)
    {
		if (array_key_exists($name, $this->fields)) {
			$this->fields[$name]->setValue($value);
		}
		else {
			$class = get_called_class();
			throw new \ErrorException("Field '{$name}' not found in '{$class}'");
		}
	}
}
