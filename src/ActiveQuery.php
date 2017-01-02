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
		$record = new $this->className();
		$record->setValues($attributes);
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
    public function by(string $relationName, array $where)
    {
		$relations = $this->className::getRelations();
        $relation = $relations[$relationName] ?? null;
        if (!$relation) {
            throw new \Exception("Relation '{$relationName}' not found");
        }

		$t1 = $this->db->quote($this->className::getTableName());
		$c1 = $this->db->quote($relation->getSelfColumn());
		$t2 = $this->db->quote($relation->getTargetTable());
		$c2 = $this->db->quote($relation->getTargetColumn());

		$this->innerJoin($t2, "{$t1}.{$c1} = {$t2}.{$c2}");
		$this->where($where);

		return $this;
    }
}
