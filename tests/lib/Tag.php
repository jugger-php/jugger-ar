<?php

namespace tests;

use jugger\ar\ActiveRecord;
use jugger\ar\field\TextField;
use jugger\ar\field\IntegerField;
use jugger\ar\relations\ManyRelation;

class Tag extends ActiveRecord
{
    public static function getTableName()
    {
        return 'tag';
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
                'column' => 'value',
                'length' => 100,
            ]),
        ];
    }

    public static function getRelations()
    {
        return [
            'authors' => (new CrossRelation('id'))->via('id_tag', 'id_post', 'post_tag')->target('id', 'tests\Post'),
        ];
    }
}
