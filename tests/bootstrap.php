<?php

use jugger\di\Di as DiContainer;
use jugger\di\Container;
use jugger\db\ConnectionPool;

// composer vendor autoload
include __DIR__ .'/../../../autoload.php';

class Di
{
    public static $pool;
}

Di::$pool = new ConnectionPool([
    'default' => [
        'class' => 'jugger\db\driver\MysqliConnection',
        'host' => 'localhost',
        'dbname' => 'test',
        'username' => 'root',
        'password' => '',
    ],
]);

DiContainer::$c = new Container([
    'db' => [
        'class' => 'jugger\db\driver\MysqliConnection',
        'host' => 'localhost',
        'dbname' => 'test',
        'username' => 'root',
        'password' => '',
    ],
]);
DiContainer::$c->query = function($c) {
    return new \jugger\db\Query($c->db);
};
