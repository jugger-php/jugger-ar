<?php

namespace jugger\ar;

use Exception;
use ArrayAccess;
use jugger\db\QueryBuilder;
use jugger\db\ConnectionPool;
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

	/**
	 * not working
	 */
	public static function getSqlCreateTable()
	{
		$fileds = static::getFields();
		$tableName = static::getTableName();

		$sql = "CREATE TABLE `{$tableName}` (\n";
		$count = count($fields);
		foreach ($fields as $field) {
			$params = [];

			$params = implode(' ', $params);

			$sql .= "`{$field->column}` {$params}";

			$count--;
			if ($count != 0) {
				$sql .= ",";
			}
			$sql .= "\n";
		}
		return $sql .")";
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

	public static abstract function getTableName();

	public static function getPrimaryKey()
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

		if ($this->isNewRecord) {
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
		$values = [];
		// исключаем PK если он не указан
		foreach ($this->fields as $name => $column) {
			$value = $column->getValue();
			if (is_null($value) && $column->autoIncrement) {
				continue;
			}
			$values[$name] = $value;
		}

		$db = ConnectionPool::get('default');
		$db->beginTransaction();

        QueryBuilder::insert(
			static::getTableName(),
			$values
		)->execute();

		$pk = static::getPrimaryKey();
		$this->$pk = $db->getLastInsertId(static::getTableName());

		$db->commit();
		$this->isNewRecord = false;

		return $this->$pk;
	}

	public function update()
    {
        $pk = static::getPrimaryKey();
        return (bool) QueryBuilder::update(
            static::getTableName(),
            $this->getValues(),
            [
                $pk => $this->$pk,
            ]
        )->execute();
	}

	public function delete()
	{
		$pk = static::getPrimaryKey();
		return QueryBuilder::delete(
			static::getTableName(),
            [$pk => $this->$pk]
		)->execute();
	}

	public static function find()
    {
        $class = get_called_class();
        $table = $class::getTableName();
        $fields = array_map(function(BaseField $row) use($table) {
            return "{$table}.{$row->column}";
        }, $class::getFields());

		return (new ActiveQuery($class))->select($fields)->from([$table]);
	}

	public static function findOne($where)
    {
		if (is_scalar($where)) {
			$where = [
				static::getPrimaryKey() => $where
			];
		}
		return static::find()->where($where)->one();
	}

	public static function findAll(array $where)
    {
		return static::find()->where($where)->all();
	}
}
