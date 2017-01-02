<?php

namespace jugger\ar\relations;

use jugger\ar\ActiveRecord;

class CrossRelation
{
    protected $vias = [];
    protected $target;
    protected $selfColumn;

    public function __construct(string $selfColumn)
    {
        $this->selfColumn = $selfColumn;
    }

    public function getVias(ActiveRecord $model)
    {
        $ret = [];
        $c1 = $this->selfColumn;
        $t1 = $model::getTableName();

        foreach ($this->vias as $via) {
            $c2 = $via['prevColumn'];
            $t2 = $via['table'];

            array_unshift($ret, [$t1, $c1, $t2, $c2]);
            $t1 = $t2;
            $c1 = $via['nextColumn'];
        }

        $c2 = $this->target['column'];
        $t2 = $this->target['class']::getTableName();
        array_unshift($ret, [$t1, $c1, $t2, $c2]);
        return $ret;
    }

    public function getQuery(ActiveRecord $model)
    {
        $class = $this->target['class'];
        $db = $class::getDb();
        $vias = $this->getVias($model);
        $query = $class::find();

        foreach ($vias as $via) {
            list($t2, $c2, $t1, $c1) = $via;
            $query->innerJoin(
                $db->quote($t2),
                $db->quote("{$t1}.{$c1}") ." = ". $db->quote("{$t2}.{$c2}")
            );
        }
        $query->where([
            "{$t2}.{$c2}" => $model[$c2]
        ]);

        return $query;
    }

    public function getValue(ActiveRecord $model)
    {
        return $this->getQuery($model)->all();
    }

    /**
     * Промежуточная таблица
     */
    public function via(string $prevColumn, string $nextColumn, string $table)
    {
        $this->vias[] = compact('prevColumn', 'nextColumn', 'table');
        return $this;
    }

    /**
     * Конечная таблица
     */
    public function target(string $column, string $class)
    {
        $this->target = compact('column', 'class');
        return $this;
    }
}
