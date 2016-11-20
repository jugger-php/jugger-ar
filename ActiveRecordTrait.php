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
            return $this->getRelation($name);
		}
		else {
			$class = get_called_class();
			throw new \ErrorException("Field or relation '{$name}' not found in '{$class}'");
		}
	}

    protected function getRelation($name)
    {
        if (is_array($this->relations[$name])) {
            $class = $this->relations[$name]['class'];
            $self = key($this->relations[$name]['relation']);
            $target = current($this->relations[$name]['relation']);
            $many = $this->relations[$name]['many'] ?? false;

            if ($many) {
                $this->relations[$name] = $class::findAll([
                    $target => $this->$self
                ]);
            }
            else {
                $this->relations[$name] = $class::findOne([
                    $target => $this->$self
                ]);
            }
        }
        return $this->relations[$name];
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
