<?php

namespace jugger\ar\mapping;

use jugger\base\Configurator;

class AssociationKey
{
    protected $keys = [];

    public function getKeys()
    {
        return $this->keys;
    }

    public function __construct(array $keys)
    {
        foreach ($keys as $key) {
            $this->addKeyArray($key);
        }
    }

    public function addKeyArray(array $config)
    {
        $selfField = $config[0];
        $targetField = $config[1];
        $targetTable = $config[2];

        $this->keys[] = new ForeignKey($selfField, $targetField, $targetTable);
    }
}
