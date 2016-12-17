<?php

namespace jugger\ar\mapping;

use jugger\base\Configurator;

class ForeignKey
{
    protected $selfField;
    protected $targetField;
    protected $targetTable;

    public function getSelfField()
    {
        return $this->selfField;
    }

    public function getTargetField()
    {
        return $this->targetField;
    }

    public function getTargetTable()
    {
        return $this->targetTable;
    }

    public function __construct($selfField, $targetField, $targetTable)
    {
        $this->selfField = $selfField;
        $this->targetField = $targetField;
        $this->targetTable = $targetTable;
    }
}
