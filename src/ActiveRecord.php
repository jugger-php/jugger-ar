<?php

namespace jugger\ar;

use Exception;
use ArrayAccess;
use jugger\db\Query;
use jugger\ar\field\BaseField;
use jugger\base\ArrayAccessTrait;

abstract class ActiveRecord implements ArrayAccess
{
	use ActiveRecordTrait;
	use ArrayAccessTrait;

    public $isNewRecord = false;

    private $fields;
    private $relations;
    private static $primaryKey;

	public function __construct(array $values = [])
    {
		$this->isNewRecord = true;
        $this->relations = static::getRelations();
        $this->initFields(static::getFields());
		$this->setFields($values);
	}

    abstract public static function getFields();

    public static function getRelations() {
        return [];
    }

    public function setFields(array $values)
    {
		foreach ($values as $name => $value) {
            $name = strtolower($name);
			if (isset($this->$name)) {
				$this->$name = $value;
			}
		}
	}

    private function initFields(array $fields)
    {
        if (!empty($this->fields)) {
            return;
        }

        $this->fields = [];
        foreach ($fields as $field) {
            $this->fields[$field->column] = $field;
        }
    }

    public function getColumns()
    {
		return array_keys($this->fields);
	}

    public function getValues()
    {
        $values = [];
        foreach ($this->fields as $column => $value) {
            $column = strtolower($column);
            $values[$column] = $value->getValue();
        }
        return $values;
    }

	public static abstract function tableName();

	public static function primaryKey()
    {
		if (!self::$primaryKey) {
            $fields = static::getFields();
            foreach ($fields as $column => $field) {
                if ($field->primary) {
                    self::$primaryKey = strtolower($field->column);
                    break;
                }
            }

            if (is_null(self::$primaryKey)) {
                throw new Exception("Not set primary key");
            }
        }
        return self::$primaryKey;
	}

    public function beforeSave()
    {
        return true;
    }

	public function save()
    {
		if (!$this->beforeSave()) {
			return false;
		}

		if ($this->$isNewRecord) {
			$ret = $this->insert();
		}
		else {
			$ret = $this->update();
		}

		$this->afterSave();
		return $ret;
	}

    public function afterSave()
    {
        // pass
    }

	public function insert()
    {
        return (new Query())->insert(
            static::tableName(),
            $this->getValues()
        );
	}

	public function update()
    {
        $pk = static::primaryKey();
        return (new Query())->insert(
            static::tableName(),
            $this->getValues(),
            [
                $pk => $this->$pk,
            ]
        );
	}

	public static function find()
    {
        $class = get_called_class();
        $table = static::tableName();
        $fields = array_map(function(BaseField $row) use($table) {
            return "{$table}.{$row->column}";
        }, static::getFields());

		return (new ActiveQuery($class))->select($fields)->from([$table]);
	}

	public static function findOne($where)
    {
		if (is_scalar($where)) {
			$where = [
				static::primaryKey() => $where
			];
		}
		return static::find()->where($where)->one();
	}

	public static function findAll(array $where)
    {
		return static::find()->where($where)->all();
	}
}
