<?php

namespace jugger\ar\relations;

use jugger\ar\ActiveRecord;

/**
 * Связь один ко многим
 */
class ManyRelation implements RelationInterface
{
    protected $vias = [];

    public function __construct(string $selfColumn, string $targetColumn, string $targetClass)
    {
        $this->next($selfColumn, $targetColumn, $targetClass);
    }

    public function next(string $selfColumn, string $targetColumn, string $targetClass)
    {
        $this->vias[] = compact('selfColumn', 'targetColumn', 'targetClass');
        return $this;
    }

    public function getValue(ActiveRecord $model)
    {
        return $this->getQuery($model)->all();
    }

    public function getQuery(ActiveRecord $model)
    {
        $db = $model::getDb();
        $vias = $this->vias;
        $joins = [];
        $where = [];

        $t1 = $model::getTableName();
        $isFirst = true;
        foreach ($vias as $via) {
            $lastClass = $via['targetClass'];

            $c1 = $via['selfColumn'];
            $c2 = $via['targetColumn'];
            $t2 = $via['targetClass']::getTableName();

            $on1 = $db->quote("{$t1}.{$c1}");
            $on2 = $db->quote("{$t2}.{$c2}");

            if ($isFirst) {
                $isFirst = false;
                $where = [
                    "{$t2}.{$c2}" => $model->$c1
                ];
                $t1 = $t2;
                continue;
            }

            array_unshift($joins, [
                $db->quote($t1),
                "{$on1} = {$on2}"
            ]);

            $t1 = $t2;
        }

        $query = $lastClass::find();
        foreach ($joins as $join) {
            list($table, $on) = $join;
            $query->innerJoin($table, $on);
        }
        $query->where($where);

        return $query;
    }

    public function getJoins(string $modelClass)
    {
        $db = $modelClass::getDb();
        $joins = [];
        $vias = $this->vias;

        $t1 = $modelClass::getTableName();
        foreach ($vias as $via) {
            $c1 = $via['selfColumn'];
            $c2 = $via['targetColumn'];
            $t2 = $via['targetClass']::getTableName();

            $on1 = $db->quote("{$t1}.{$c1}");
            $on2 = $db->quote("{$t2}.{$c2}");

            $joins[] = [
                $db->quote($t2),
                "{$on1} = {$on2}"
            ];

            $t1 = $t2;
        }

        return $joins;
    }
}
