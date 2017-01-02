<?php

namespace jugger\ar\relations;

use jugger\ar\ActiveRecord;

abstract class Relation
{
    protected $many;
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
        $class = $this->targetClass;
        return $class::getTableName();
    }

    public function getQuery(ActiveRecord $model)
    {
        $tableName = $this->getTargetTable();
        $where = [
            "{$tableName}.{$this->targetColumn}" => $model[$this->selfColumn]
        ];

        return $this->targetClass::find()->where($where);
    }

    public function getValue(ActiveRecord $model)
    {
        if ($this->many) {
            return $this->getQuery($model)->all();
            // return $this->targetClass::findAll($where);
        }
        else {
            return $this->getQuery($model)->one();
            // return $this->targetClass::findOne($where);
        }
    }
}
