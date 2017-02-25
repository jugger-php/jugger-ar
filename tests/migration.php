<?php

use PHPUnit\Framework\TestCase;
use jugger\ar\tools\Migration;

include_once __DIR__.'/records.php';

class MigrationTest extends TestCase
{
    public function testBase()
    {
        // $this->assertEquals(
        //     Migration::getCreateTableSql(Post::class),
        //     "CREATE TABLE `post`(id INT NOT NULL PRIMARY KEY,title VARCHAR(100) NOT NULL,content TEXT )"
        // );
        // $this->assertEquals(
        //     Migration::getCreateTableSql(Post::class),
        //     "CREATE TABLE `post`(id INT NOT NULL PRIMARY KEY,title VARCHAR(100) NOT NULL,content TEXT )"
        // );
        // $this->assertEquals(
        //     Migration::getCreateTableSql(Post::class),
        //     "CREATE TABLE `post`(id INT NOT NULL PRIMARY KEY,title VARCHAR(100) NOT NULL,content TEXT )"
        // );
    }
}
