<?php

use PHPUnit\Framework\TestCase;
use jugger\ar\tools\Generator;

class GeneratorTest extends TestCase
{
    public function createTables()
    {
        $sqls = [
            "DROP TABLE IF EXISTS `post_category`",
            "DROP TABLE IF EXISTS `post`",
            // category
            "CREATE TABLE `post_category` (
              `id` INT NOT NULL AUTO_INCREMENT,
              `name` CHAR(50) NOT NULL,
              `time` DATETIME NOT NULL DEFAULT NOW(),
              PRIMARY KEY (`id`))
            ENGINE = InnoDB",
            // post
            "CREATE TABLE `post` (
              `id` INT NOT NULL AUTO_INCREMENT,
              `category_id` INT NOT NULL,
              `title` VARCHAR(100) NOT NULL,
              `content` TEXT NULL,
              `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `raiting` FLOAT NOT NULL DEFAULT 0,
              `views` BIGINT(20) NULL DEFAULT 0,
              PRIMARY KEY (`id`),
              INDEX `fk_post_category1_idx` (`category_id` ASC),
              CONSTRAINT `fk_post_post_category1`
                FOREIGN KEY (`category_id`)
                REFERENCES `post_category` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION)
            ENGINE = InnoDB",
        ];

        foreach ($sqls as $sql) {
            Di::$pool['default']->execute($sql);
        }
    }

    public function testBase()
    {
        $this->createTables();

        echo Generator::buildClassMysql('post_category');
        echo Generator::buildClassMysql('post');

        // $this->assertEquals(
        //     Generator::buildClassMysql('post_category'),
        //     ""
        // );
        // $this->assertEquals(
        //     Generator::buildClassMysql('post'),
        //     ""
        // );
    }
}
