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
            $data = $this->relations[$name];
            $class = $data['class'];
            $self = key($data['relation']);
            $target = current($data['relation']);
            $many = $data['many'] ?? false;

            if ($many) {
                return $class::findAll([
                    $target => $this->$self
                ]);
            }
            else {
                return $class::findOne([
                    $target => $this->$self
                ]);
            }
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
