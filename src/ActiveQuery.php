<?php

namespace jugger\ar;

use jugger\db\Query;
use jugger\db\ConnectionInterface;
use jugger\ar\relations\RelationInterface;
use jugger\ar\mapping\ForeignKey;
use jugger\ar\mapping\AssociationKey;

class ActiveQuery extends Query
{
	protected $className;

	public function __construct(ConnectionInterface $db, string $className)
    {
		parent::__construct($db);

		$this->className = $className;
		$this->from($className::getTableName());
	}

	protected function createRecord(array $attributes)
	{
		$class = $this->className;
		$record = new $class();
		$record->setValues($attributes);
		return $record;
	}

	public function one()
	{
		$row = parent::one();
		if (!$row) {
			return null;
		}
		return $this->createRecord($row);
	}

	public function all(): array
	{
		$class = $this->className;
		$result = $this->query();
		$rows = [];
		$pk = $class::getPrimaryKey();
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
    public function by(string $relationName, array $where)
    {
		$class = $this->className;
        $relation = $class::getRelations()[$relationName] ?? null;
        if (!$relation) {
            throw new \Exception("Relation '{$relationName}' not found");
        }

		$joins = $relation->getJoins($class);
		foreach ($joins as $join) {
			list($table, $on) = $join;
			$this->innerJoin($table, $on);
		}
		$this->andWhere($where);

		return $this;
    }

	public function __call($name, array $arguments)
	{
		$prefix = substr($name, 0, 2);
		if ($prefix == 'by') {
			$relationName = strtolower(substr($name, 2));
			return $this->by($relationName, $arguments[0]);
		}
		else {
			throw new \ErrorException("Method '{$name}' not found");
		}
	}
}
