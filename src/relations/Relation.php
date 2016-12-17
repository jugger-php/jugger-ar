<?php

namespace jugger\ar\relations;

use jugger\ar\mapping\ForeignKey;

abstract class Relation extends ForeignKey implements RelationInterface
{
    protected $many;
    protected $value;

    public function getTargetTable()
    {
        $class = $this->targetTable;
        return $class::getTableName();
    }

    public function getValue($selfValue)
    {
        if (!$this->value) {
            $class = $this->targetTable;
            $params = [
                $this->targetField => $selfValue
            ];

            if ($this->many) {
                $this->value = $class::findAll($params);
            }
            else {
                $this->value = $class::findOne($params);
            }
        }
        return $this->value;
    }
}
