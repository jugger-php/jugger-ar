<?php

namespace jugger\ar\relations;

use jugger\ar\ActiveRecord;

/**
 * Связь один к одному
 */
class OneRelation implements RelationInterface
{
    protected $selfColumn;
    protected $targetClass;
    protected $targetColumn;

    public function __construct(string $selfColumn, string $targetColumn, string $targetClass)
    {
        $this->selfColumn = $selfColumn;
        $this->targetClass = $targetClass;
        $this->targetColumn = $targetColumn;
    }

    public function getSelfColumn()
    {
        return $this->selfColumn;
    }

    public function getTargetColumn()
    {
        return $this->targetColumn;
    }

    public function getTargetTable()
    {
        return $this->targetClass::getTableName();
    }

    public function getQuery(ActiveRecord $model)
    {
        $c1 = $this->getSelfColumn();
        $t2 = $this->getTargetTable();
        $c2 = $this->getTargetColumn();

        $class = $this->targetClass;
        return $class::find()->where([
            "{$t2}.{$c2}" => $model->$c1
        ]);
    }

    public function getValue(ActiveRecord $model)
    {
        return $this->getQuery($model)->one();
    }

    public function getJoins(string $modelClass)
    {
        $db = $modelClass::getDb();

        $t1 = $modelClass::getTableName();
        $c1 = $this->getSelfColumn();
        $c2 = $this->getTargetColumn();
        $t2 = $this->getTargetTable();

        $on1 = $db->quote("{$t1}.{$c1}");
        $on2 = $db->quote("{$t2}.{$c2}");

        return [
            [
                $db->quote($t2),
                "{$on1} = {$on2}"
            ]
        ];
    }
}
