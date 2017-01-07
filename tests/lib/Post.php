<?php

namespace tests;

use jugger\db\ConnectionInterface;
use jugger\ar\ActiveRecord;
use jugger\ar\relations\ManyRelation;
use jugger\ar\validator\PrimaryValidator;
use jugger\model\Model;
use jugger\model\field\TextField;
use jugger\model\field\IntField;
use jugger\model\validator\RangeValidator;

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
                'validators' => [
                    new PrimaryValidator(),
                ],
            ]),
            new TextField([
                'name' => 'title',
                'validators' => [
                    new RangeValidator(1, 100)
                ],
            ]),
            new TextField([
                'name' => 'content',
            ]),
        ];
    }

    public static function getRelations(): array
    {
        return [
            'authors' => new ManyRelation('id', 'id_post', 'tests\Author'),
        ];
    }
}
