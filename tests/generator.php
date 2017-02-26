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

        $codePost = preg_replace("/\r\n|\n/", " ", Generator::buildClassMysql('post'));
        $codeCategory = preg_replace("/\r\n|\n/", " ", Generator::buildClassMysql('post_category'));

        $this->assertEquals(
            $codePost,
            "<?php  use jugger\\ar\\ActiveRecord; use jugger\\ar\\relations\\OneRelation; use jugger\\model\\field\\DatetimeField; use jugger\\model\\field\\FloatField; use jugger\\model\\field\\IntField; use jugger\\model\\field\\TextField; use jugger\\model\\validator\\PrimaryValidator; use jugger\\model\\validator\\RangeValidator; use jugger\\model\\validator\\RequireValidator;  class Post extends ActiveRecord {     public static function getTableName(): string;     {         return 'post';     }      public static function getSchema(): array     {         return [             new IntField([                 'name' => 'id',                 'validators' => [                     new PrimaryValidator(),                 ],             ]),             new IntField([                 'name' => 'category_id',                 'validators' => [                     new RequireValidator(),                 ],             ]),             new TextField([                 'name' => 'title',                 'validators' => [                     new RequireValidator(),                     new RangeValidator(0, 100),                 ],             ]),             new TextField([                 'name' => 'content',                 'validators' => [                     new RangeValidator(0, 65535),                 ],             ]),             new DatetimeField([                 'name' => 'time',                 'value' => 'CURRENT_TIMESTAMP',                 'format' => 'timestamp',                 'validators' => [                     new RequireValidator(),                 ],             ]),             new FloatField([                 'name' => 'raiting',                 'validators' => [                     new RequireValidator(),                 ],             ]),             new IntField([                 'name' => 'views',             ]),         ];     }          public static function getRelations(): array     {         return [             'post_category' => new OneRelation('category_id', 'id', 'PostCategory'),         ];     }      } "
        );
        $this->assertEquals(
            $codeCategory,
            "<?php  use jugger\\ar\\ActiveRecord; use jugger\\ar\\relations\\ManyRelation; use jugger\\model\\field\\DatetimeField; use jugger\\model\\field\\IntField; use jugger\\model\\field\\TextField; use jugger\\model\\validator\\PrimaryValidator; use jugger\\model\\validator\\RangeValidator; use jugger\\model\\validator\\RequireValidator;  class PostCategory extends ActiveRecord {     public static function getTableName(): string;     {         return 'post_category';     }      public static function getSchema(): array     {         return [             new IntField([                 'name' => 'id',                 'validators' => [                     new PrimaryValidator(),                 ],             ]),             new TextField([                 'name' => 'name',                 'validators' => [                     new RequireValidator(),                     new RangeValidator(0, 50),                 ],             ]),             new DatetimeField([                 'name' => 'time',                 'value' => 'CURRENT_TIMESTAMP',                 'format' => 'Y-m-d H:i:s',                 'validators' => [                     new RequireValidator(),                 ],             ]),         ];     }          public static function getRelations(): array     {         return [             'posts' => new ManyRelation('id', 'category_id', 'Post'),         ];     }      } "
        );
    }
}
