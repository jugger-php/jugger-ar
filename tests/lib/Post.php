<?php

namespace tests;

use jugger\ar\ActiveRecord;
use jugger\ar\field\TextField;
use jugger\ar\field\IntegerField;
use jugger\ar\relations\ManyRelation;

class Post extends ActiveRecord
{
    public static function getTableName()
    {
        return 'post';
    }

    public static function getDb()
    {
        return static::$_db ?? \Di::$pool['default'];
    }

    public static function getSchema()
    {
        return [
            new IntegerField([
                'column' => 'id',
                'primary' => true,
                'autoIncrement' => true,
            ]),
            new TextField([
                'column' => 'title',
                'length' => 100,
            ]),
            new TextField([
                'column' => 'content',
                'default' => null,
            ]),
        ];
    }

    public static function getRelations()
    {
        return [
            'authors' => new ManyRelation('id', 'id_post', 'tests\Author'),
        ];
    }
}
