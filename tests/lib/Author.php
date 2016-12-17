<?php

namespace tests;

use jugger\ar\ActiveRecord;
use jugger\ar\field\TextField;
use jugger\ar\field\IntegerField;
use jugger\ar\relations\OneRelation;

class Author extends ActiveRecord
{
    public static function getTableName()
    {
        return 'author';
    }

    public static function getFields()
    {
        return [
            new IntegerField([
                'column' => 'id',
                'primary' => true,
                'autoIncrement' => true,
            ]),
            new IntegerField([
                'column' => 'id_post',
            ]),
            new TextField([
                'column' => 'name',
                'default' => null,
            ]),
        ];
    }

    public static function getRelations()
    {
        return [
            'post' => new OneRelation('id_post', 'id', 'tests\Post'),
        ];
    }
}
