<?php

namespace jugger\ar\relations;

use jugger\ar\ActiveRecord;

interface RelationInterface
{
    public function getQuery(ActiveRecord $model);

    public function getValue(ActiveRecord $model);

    public function getJoins(string $tableName);
}
