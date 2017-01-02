<?php

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

spl_autoload_register(function($class) {
    if (substr($class, 0, 5) != "tests") {
        return;
    }

    $class = substr($class, 6);
    $file = __DIR__ ."/lib/{$class}.php";
    if (file_exists($file)) {
        require $file;
    }
});
