<?php

namespace jugger\ar;

use jugger\db\Command;
use jugger\db\ConnectionPool;
use jugger\ar\field\BaseField;
use jugger\base\ArrayAccessTrait;

abstract class ActiveRecord implements \ArrayAccess
{
	use ActiveRecordTrait;
	use ArrayAccessTrait;

	protected $_fields;
	protected static $_db;
	protected static $_primaryKey;

	public function __construct(array $values = [])
    {
		$this->setValues($values);
	}

	public function isNewRecord()
	{
		$primaryKey = static::getPrimaryKey()->getColumn();
		return is_null($this->$primaryKey);
	}

	public static function setDb(ConnectionInterface $db)
	{
		static::$_db = $db;
	}

	public static function getDb()
	{
		return static::$_db;
	}

    abstract public static function getSchema();

	public function getFields()
	{
		if (!$this->_fields) {
			$this->_fields = [];
			$schema = static::getSchema();
			foreach ($schema as $value) {
				$key = $value->getColumn();
				$this->_fields[$key] = $value;
			}
		}
		return $this->_fields;
	}

	public function getField($name)
	{
		$fields = $this->getFields();
		return $fields[$name] ?? null;
	}

    public function getColumns()
    {
		return array_keys($this->getFields());
	}

	public function setValues(array $values)
    {
		foreach ($values as $name => $value) {
            $name = strtolower($name);
			if (isset($this->$name)) {
				$this->$name = $value;
			}
		}
	}

    public function getValues()
    {
        $values = [];
        foreach ($this->getFields() as $column => $field) {
            $values[$column] = $field->getValue();
        }
        return $values;
    }

	public static function getRelations()
	{
        return [];
    }

	public static abstract function getTableName();

	public static function getPrimaryKey()
    {
		if (!static::$_primaryKey) {
            $fields = static::getSchema();
            foreach ($fields as $column => $field) {
                if ($field->primary) {
                    static::$_primaryKey = $field;
                    break;
                }
            }
            if (is_null(static::$_primaryKey)) {
                throw new \Exception("Not set primary key");
            }
        }
        return static::$_primaryKey;
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

		if ($this->isNewRecord()) {
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
		$db = static::getDb();
		$values = $this->getValues();
		$tableName = static::getTableName();
		$primaryKey = static::getPrimaryKey()->getColumn();

		// $db->execute("LOCK TABLES `{$tableName}`");
		$ret = (new Command($db))->insert($tableName, $values)->execute();
		$this->$primaryKey = $db->getLastInsertId();
		// $db->execute("UNLOCK TABLES");

		return $ret;
	}

	public function update()
    {
		$values = $this->getValues();
		$primaryKey = static::getPrimaryKey()->getColumn();

		return static::updateAll($values, [
			$primaryKey => $this->$primaryKey
		]);
	}

	public static function updateAll(array $values, $where)
	{
		$db = static::getDb();
		$tableName = static::getTableName();
		return (new Command($db))->update($tableName, $values, $where)->execute();
	}

	public function delete()
	{
		if ($this->isNewRecord()) {
			return false;
		}

		$primaryKey = static::getPrimaryKey()->getColumn();
		return static::deleteAll([
			$primaryKey => $this->$primaryKey
		]);
	}

	public static function deleteAll($where)
	{
		$db = static::getDb();
		$tableName = static::getTableName();
		return (new Command($db))->delete($tableName, $where)->execute();
	}

	public static function find(ConnectionInterface $db = null)
    {
		$db = $db ?? static::getDb();
        $tableName = static::getTableName();
        $fields = array_map(
			function(BaseField $field) use($tableName) {
	            return "{$tableName}.{$field->getColumn()}";
	        },
			static::getSchema()
		);

		$class = get_called_class();
		return (new ActiveQuery($db, $class))->select($fields)->from([$tableName]);
	}

	public static function findOne($where = null)
    {
		if (empty($where)) {
			return static::find()->one();
		}
		elseif (is_scalar($where)) {
			$where = [
				static::getPrimaryKey()->getColumn() => $where
			];
		}
		return static::find()->where($where)->one();
	}

	public static function findAll($where = null)
    {
		if (empty($where)) {
			return static::find()->all();
		}
		return static::find()->where($where)->all();
	}
}
