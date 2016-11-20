<?php

namespace jugger\ar;

interface QueryInterface
{
    public function select($value);

    public function from($value);

    public function join($type, $table, $on);

    public function innerJoin($table, $on);

    public function leftJoin($table, $on);

    public function rightJoin($table, $on);

    public function where(array $value);

    public function andWhere($value);

    public function orWhere($value);

    public function groupBy($value);

    public function having($value);

    public function orderBy($value);

    public function limit($limit, $offset);

    public function build();

    public function query();

    public function one();

    public function all();
}
