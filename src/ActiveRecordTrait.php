<?php

namespace jugger\ar;

trait ActiveRecordTrait
{
    public function __isset($name)
    {
        $fields = $this->getFields();
		if (array_key_exists($name, $fields)) {
			return true;
		}

        $relations = static::getRelations();
        if (array_key_exists($name, $relations)) {
            return true;
		}

        return false;
	}

    public function __unset($name)
    {
        $fields = $this->getFields();
		if (array_key_exists($name, $fields)) {
			$fields[$name]->setValue(null);
		}
    }

	public function __get($name)
    {
        $fields = $this->getFields();
		if (array_key_exists($name, $fields)) {
			return $fields[$name]->getValue();
		}

        $relations = static::getRelations();
        if (array_key_exists($name, $relations)) {
            return $relations[$name]->getValue($this);
		}

		$class = get_called_class();
		throw new \ErrorException("Field or relation '{$name}' not found");
	}

	public function __set($name, $value)
    {
        $fields = $this->getFields();
		if (array_key_exists($name, $fields)) {
			$fields[$name]->setValue($value);
            return;
		}

        $relations = static::getRelations();
        if (array_key_exists($name, $relations)) {
            throw new \Exception("Field '{$name}' is relation! Must not set value for relation");
		}

		$class = get_called_class();
		throw new \Exception("Field '{$name}' not found");
	}
}
