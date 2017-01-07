<?php

namespace tests;

use jugger\db\ConnectionInterface;
use jugger\ar\ActiveRecord;
use jugger\ar\relations\ManyRelation;
use jugger\model\Model;
use jugger\model\field\TextField;
use jugger\model\field\IntField;
use jugger\model\validator\RangeValidator;

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
                'validators' => [
                    new PrimaryValidator()
                ],
            ]),
            new TextField([
                'name' => 'value',
                'validators' => [
                    new RangeValidator(1, 100)
                ],
            ]),
        ];
    }

    public static function getRelations(): array
    {
        return [
            'authors' => (new CrossRelation('id'))->via('id_tag', 'id_post', 'post_tag')->target('id', 'tests\Post'),
        ];
    }
}
