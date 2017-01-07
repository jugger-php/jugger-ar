<?php

use PHPUnit\Framework\TestCase;
use jugger\ar\ActiveRecord;
use jugger\ar\relations\OneRelation;
use jugger\ar\relations\ManyRelation;
use jugger\ar\relations\CrossRelation;
use jugger\model\Model;
use jugger\model\field\TextField;
use jugger\model\field\IntField;
use jugger\db\ConnectionInterface;

class Category extends ActiveRecord
{
    public static function getTableName(): string
    {
        return 'category';
    }

    public static function getDb(): ConnectionInterface
    {
        return static::$_db ?? \Di::$pool['default'];
    }

    public static function getSchema(): array
    {
        return [
            new IntField([
                'name' => 'id',
            ]),
        ];
    }
}

class Tag extends ActiveRecord
{
    public static function getTableName(): string
    {
        return 'tag';
    }

    public static function getDb(): ConnectionInterface
    {
        return static::$_db ?? \Di::$pool['default'];
    }

    public static function getSchema(): array
    {
        return [
            new IntField([
                'name' => 'id',
            ]),
            new TextField([
                'name' => 'name',
            ]),
        ];
    }
}

class Post extends ActiveRecord
{
    public static function getTableName(): string
    {
        return 'post';
    }

    public static function getDb(): ConnectionInterface
    {
        return static::$_db ?? \Di::$pool['default'];
    }

    public static function getSchema(): array
    {
        return [
            new IntField([
                'name' => 'id',
            ]),
            new IntField([
                'name' => 'id_category',
            ]),
        ];
    }
}

class RelationsTest extends TestCase
{
    public function testBase()
    {
        $post = new Post([
            'id' => '123',
            'id_category' => '456',
        ]);
        $postRelation = new OneRelation('id_category', 'id', 'Category');
        $this->assertEquals(
            $postRelation->getQuery($post)->build(),
            "SELECT `category`.`id` FROM `category` WHERE (`category`.`id` = '456')"
        );

        $category = new Category(['id' => '456']);
        $categoryRelation = new ManyRelation('id', 'id_category', 'Post');
        $this->assertEquals(
            $categoryRelation->getQuery($category)->build(),
            "SELECT `post`.`id`, `post`.`id_category` FROM `post` WHERE (`post`.`id_category` = '456')"
        );

        $tagsRelation = (new CrossRelation('id'))->via('id_post', 'id_tag', 'post_to_tag')->target('id', 'Tag');
        $this->assertEquals(
            $tagsRelation->getQuery($post)->build(),
            "SELECT `tag`.`id`, `tag`.`name` FROM `tag` INNER JOIN `post_to_tag` ON `tag`.`id` = `post_to_tag`.`id_tag`  INNER JOIN `post` ON `post_to_tag`.`id_post` = `post`.`id`  WHERE (`post`.`id` = '123')"
        );

        $categoryRelation = (new CrossRelation('id'))
            ->via('post_id', 'tag_id', 'post_tag')
            ->via('tag_id', 'category_id', 'tag_category')
            ->target('id', 'Category');
        $this->assertEquals(
            $categoryRelation->getQuery($post)->build(),
            "SELECT `category`.`id` FROM `category` INNER JOIN `tag_category` ON `category`.`id` = `tag_category`.`category_id`  INNER JOIN `post_tag` ON `tag_category`.`tag_id` = `post_tag`.`tag_id`  INNER JOIN `post` ON `post_tag`.`post_id` = `post`.`id`  WHERE (`post`.`id` = '123')"
        );
    }
}
