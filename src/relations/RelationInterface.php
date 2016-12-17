<?php

namespace jugger\ar\relations;

interface RelationInterface
{
    public function getValue($selfValue);
}
