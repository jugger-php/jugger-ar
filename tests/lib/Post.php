<?php

namespace tests;

use jugger\ar\ActiveRecord;
use jugger\ar\field\TextField;
use jugger\ar\field\IntegerField;

class Post extends ActiveRecord
{
    public static function getTableName()
    {
        return 'post';
    }

    public static function getFields()
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
            'authors' => [
                'class' => 'tests\Author',
                'relation' => ['id' => 'id_post'],
                'many' => true,
            ],
        ];
    }
}