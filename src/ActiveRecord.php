<?php

namespace jugger\ar;

use jugger\ar\validator\PrimaryValidator;
use jugger\db\Command;
use jugger\db\ConnectionInterface;
use jugger\model\Model;
use jugger\model\field\BaseField;

abstract class ActiveRecord extends Model
{
	use ActiveRecordTrait;

	protected static $_primaryKey;

	public function isNewRecord(): bool
	{
		$primaryKey = static::getPrimaryKey()->getName();
		return is_null($this->$primaryKey);
	}

	public static function getDb(): ConnectionInterface
	{
		return \jugger\di\Di::$c->db;
	}

	public static function getRelations(): array
	{
        return [];
    }

	public static abstract function getTableName(): string;

	public static function getPrimaryKey(): BaseField
    {
		if (!static::$_primaryKey) {
            $fields = static::getSchema();
            foreach ($fields as $field) {
                if ($field->existValidator(PrimaryValidator::class)) {
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

	public function save(): bool
    {
		if ($this->isNewRecord()) {
			return $this->insert();
		}
		else {
			return $this->update();
		}
	}

	protected function insert(): bool
    {
		$db = static::getDb();
		$values = $this->getValues();
		$tableName = static::getTableName();
		$primaryKey = static::getPrimaryKey()->getName();

		$ret = (new Command($db))->insert($tableName, $values)->execute();
		$this->$primaryKey = $db->getLastInsertId();

		return $ret;
	}

	protected function update(): bool
    {
		$values = $this->getValues();
		$primaryKey = static::getPrimaryKey()->getName();

		return static::updateAll($values, [
			$primaryKey => $this->$primaryKey
		]);
	}

	public static function updateAll(array $values, $where): int
	{
		$db = static::getDb();
		$tableName = static::getTableName();
		return (new Command($db))->update($tableName, $values, $where)->execute();
	}

	public function delete(): bool
	{
		if ($this->isNewRecord()) {
			return false;
		}

		$primaryKey = static::getPrimaryKey()->getName();
		return static::deleteAll([
			$primaryKey => $this->$primaryKey
		]);
	}

	public static function deleteAll($where): int
	{
		$db = static::getDb();
		$tableName = static::getTableName();
		return (new Command($db))->delete($tableName, $where)->execute();
	}

	public static function find(): ActiveQuery
    {
		$db = static::getDb();
        $tableName = static::getTableName();
        $fields = array_map(
			function(BaseField $field) use($tableName) {
	            return "{$tableName}.{$field->getName()}";
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
				static::getPrimaryKey()->getName() => $where
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
