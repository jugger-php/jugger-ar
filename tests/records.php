<?php

use jugger\ar\ActiveRecord;
use jugger\ar\relations\OneRelation;
use jugger\ar\relations\ManyRelation;
use jugger\ar\validator\PrimaryValidator;

use jugger\db\ConnectionInterface;

use jugger\model\field\TextField;
use jugger\model\field\IntField;
use jugger\model\validator\RangeValidator;
use jugger\model\validator\RequireValidator;

class Post extends ActiveRecord
{
    public static function getTableName(): string
    {
        return 'post';
    }

    public static function getDb(): ConnectionInterface
    {
        return \Di::$pool['default'];
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
                    new RequireValidator(),
                    new RangeValidator(1, 100)
                ],
            ]),
            new TextField([
                'name' => 'content',
                'value' => 'empty content',
            ]),
        ];
    }
}

class Section extends ActiveRecord
{
    public static function getTableName(): string
    {
        return 'section';
    }

    public static function getDb(): ConnectionInterface
    {
        return \Di::$pool['default'];
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
                'name' => 'name',
            ]),
        ];
    }

    public static function getRelations(): array
    {
        return [
            'elements' => (new ManyRelation('id', 'id_section', 'SectionElement'))->next('id_element', 'id', 'Element'),
        ];
    }
}

class SectionElement extends ActiveRecord
{
    public static function getTableName(): string
    {
        return 'section_element';
    }

    public static function getDb(): ConnectionInterface
    {
        return \Di::$pool['default'];
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
                'name' => 'id_element',
                'validators' => [
                    new RequireValidator()
                ],
            ]),
            new IntField([
                'name' => 'id_section',
                'validators' => [
                    new RequireValidator()
                ],
            ]),
        ];
    }

    public static function getRelations(): array
    {
        return [
            'section' => new OneRelation('id_section', 'id', 'Section'),
            'element' => new OneRelation('id_element', 'id', 'Element'),
        ];
    }
}

class Element extends ActiveRecord
{
    public static function getTableName(): string
    {
        return 'element';
    }

    public static function getDb(): ConnectionInterface
    {
        return \Di::$pool['default'];
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
                'name' => 'name',
            ]),
        ];
    }

    public static function getRelations(): array
    {
        return [
            'sections' => (new ManyRelation('id', 'id_element', 'SectionElement'))->next('id_section', 'id', 'Section'),
            'properties' => new ManyRelation('id', 'id_element', 'ElementProperty'),
        ];
    }
}

class ElementProperty extends ActiveRecord
{
    public static function getTableName(): string
    {
        return 'element_property';
    }

    public static function getDb(): ConnectionInterface
    {
        return \Di::$pool['default'];
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
                'name' => 'id_element',
            ]),
            new IntField([
                'name' => 'id_property',
            ]),
            new TextField([
                'name' => 'value',
            ]),
            new IntField([
                'name' => 'value_enum',
            ]),
        ];
    }

    public static function getRelations(): array
    {
        return [
            'element' => new OneRelation('id_element', 'id', 'Element'),
            'property' => new OneRelation('id_property', 'id', 'Property'),
        ];
    }
}

class Property extends ActiveRecord
{
    public static function getTableName(): string
    {
        return 'property';
    }

    public static function getDb(): ConnectionInterface
    {
        return \Di::$pool['default'];
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
                'name' => 'name',
            ]),
        ];
    }

    public static function getRelations(): array
    {
        return [
            'values' => new ManyRelation('id', 'id_property', 'PropertyEnum'),
        ];
    }
}

class PropertyEnum extends ActiveRecord
{
    public static function getTableName(): string
    {
        return 'property_enum';
    }

    public static function getDb(): ConnectionInterface
    {
        return \Di::$pool['default'];
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
                'name' => 'id_property',
            ]),
            new TextField([
                'name' => 'value',
            ]),
        ];
    }

    public static function getRelations(): array
    {
        return [
            'property' => new OneRelation('id_property', 'id', 'Property'),
        ];
    }
}
