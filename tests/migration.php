<?php

use PHPUnit\Framework\TestCase;
use jugger\ar\tools\Migration;

include_once __DIR__.'/records.php';

class MigrationTest extends TestCase
{
    public function testBase()
    {
        $this->assertEquals(
            Migration::getCreateTableSql(Post::class),
            "CREATE TABLE `post`(`id` INT NOT NULL PRIMARY KEY,`title` VARCHAR(100) NOT NULL,`content` TEXT DEFAULT 'empty content')"
        );
        $this->assertEquals(
            Migration::getCreateTableSql(Section::class),
            "CREATE TABLE `section`(`id` INT NOT NULL PRIMARY KEY,`name` TEXT )"
        );
        $this->assertEquals(
            Migration::getCreateTableSql(SectionElement::class),
            "CREATE TABLE `section_element`(`id` INT NOT NULL PRIMARY KEY,`id_element` INT NOT NULL,`id_section` INT NOT NULL,CONSTRAINT `fk_section_element_id_section_to_section_id` FOREIGN KEY (`id_section`) REFERENCES `section`(`id`),CONSTRAINT `fk_section_element_id_element_to_element_id` FOREIGN KEY (`id_element`) REFERENCES `element`(`id`))"
        );
    }
}
