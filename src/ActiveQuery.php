<?php

namespace jugger\ar;

use Exception;
use ReflectionClass;
use jugger\db\Query;

class ActiveQuery extends Query
{
	protected $className;

	public function __construct(string $className)
    {
		$ar = new ReflectionClass('jugger\ar\ActiveRecord');
		if ($ar->isInstance(new $className) === false) {
			throw new Exception("Class of argument must be child of 'ActiveRecord'");
		}

		$this->className = $className;
		$this->from($className::getTableName());
	}

	protected function createRecord(array $attributes) {
		$class = $this->className;
		$record = new $class();
		$record->isNewRecord = false;
		$record->setFields($attributes);
		return $record;
	}

	public function one(bool $asArray = false)
	{
		$row = parent::one();
		if ($asArray) {
			return $row;
		}
		elseif (!$row) {
			return null;
		}
		return $this->createRecord($row);
	}

	public function all(bool $asArray = false): array
	{
		if ($asArray) {
			return parent::all();
		}

		$rows = [];
		$result = $this->query();
		$pk = $this->className::getPrimaryKey();
		while ($row = $result->fetch()) {
			$rows[] = $this->createRecord($row);
		}
		return $rows;
	}

    /**
     * Дополяем запрос нужной связью и формируем запрос для объекта связи
     * @param  [type] $relationName [description]
     * @param  [type] $where        [description]
     * @return [type]               [description]
     */
    public function by(string $relationName, array $where = [])
    {
        $relation = $this->className::getRelations()[$relationName] ?? null;
        if (!$relation) {
            throw new Exception("Relation '{$relationName}' not found in '{$this->className}' class");
        }

		$relationClass = $relation['class'];
		$selfColumn = key($relation['relation']);
		$selfTable = ((array) $this->from)[0];

		$targetColumn = current($relation['relation']);
		$targetTable = $relationClass::getTableName();
		$on = "{$selfTable}.{$selfColumn} = {$targetTable}.{$targetColumn}";

		$this->innerJoin($targetTable, $on);
		if (!empty($where)) {
			$this->andWhere($where);
		}

		return $this;
    }
}
