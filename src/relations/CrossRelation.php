<?php

namespace jugger\ar\relations;

use jugger\ar\mapping\AssociationKey;

class CrossRelation extends AssociationKey implements RelationInterface
{
    protected $many;
    protected $value;
    protected $className;

    public function __construct(array $keys, string $className, bool $many)
    {
        $this->many = $many;
        $this->className = $className;
        // set table name in last index
        $keys[count($keys) - 1][2] = $className::getTableName();

        parent::__construct($keys);
    }

    public function getValue($selfValue)
    {
        if (!$this->value) {
            $class = $this->className();
            $query = $class::find();

            foreach ($this->keys as $key) {
                $query->joinForeignKey($key);
            }

            $this->value = $this->many ? $query->all() : $query->one();
        }
        return $this->value;
    }
}
