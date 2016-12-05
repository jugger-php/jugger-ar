<?php

use jugger\db\ConnectionPool;

// composer vendor autoload
include __DIR__ .'/../../../autoload.php';

ConnectionPool::getInstance()->init([
    'default' => [
        'class' => 'jugger\db\pdo\PdoConnection',
        'dsn' => 'sqlite::memory:',
    ]
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
