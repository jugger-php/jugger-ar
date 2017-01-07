<?php

namespace tests;

use jugger\ar\ActiveRecord;
use jugger\db\ConnectionInterface;
use jugger\model\Model;
use jugger\model\field\TextField;
use jugger\model\field\IntField;
use jugger\ar\relations\OneRelation;
use jugger\ar\validator\PrimaryValidator;

class Author extends ActiveRecord
{
    public static function getTableName(): string
    {
        return 'author';
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
                'validators' => [
                    new PrimaryValidator()
                ],
            ]),
            new IntField([
                'name' => 'id_post',
            ]),
            new TextField([
                'name' => 'name',
            ]),
        ];
    }

    public static function getRelations(): array
    {
        return [
            'post' => new OneRelation('id_post', 'id', 'tests\Post'),
        ];
    }
}
