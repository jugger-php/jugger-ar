<?php

use jugger\ar\ActiveRecord;
use jugger\ar\validator\PrimaryValidator;
use jugger\db\ConnectionInterface;
use jugger\model\field\TextField;
use jugger\model\field\IntField;
use jugger\ar\relations\OneRelation;
use jugger\ar\relations\ManyRelation;
use jugger\ar\relations\CrossRelation;

class Section extends ActiveRecord
{
    public static function getTableName(): string
    {
        return 'b_iblock_section';
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
            'elements' => (new ManyRelation('id', 'iblock_section_id', 'SectionElement'))->next('iblock_element_id', 'id', 'Element'),
        ];
    }
}

class SectionElement extends ActiveRecord
{
    public static function getTableName(): string
    {
        return 'b_iblock_section_element';
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
                'name' => 'iblock_element_id',
            ]),
            new IntField([
                'name' => 'iblock_section_id',
            ]),
        ];
    }

    public static function getRelations(): array
    {
        return [
            'section' => new OneRelation('iblock_section_id', 'id', 'Section'),
            'element' => new OneRelation('iblock_element_id', 'id', 'Element'),
        ];
    }
}

class Element extends ActiveRecord
{
    public static function getTableName(): string
    {
        return 'b_iblock_element';
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
            'sections' => (new ManyRelation('id', 'iblock_element_id', 'SectionElement'))->next('iblock_section_id', 'id', 'Section'),
            'properties' => new ManyRelation('id', 'iblock_element_id', 'ElementProperty'),
        ];
    }
}

class ElementProperty extends ActiveRecord
{
    public static function getTableName(): string
    {
        return 'b_iblock_element_property';
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
                'name' => 'iblock_element_id',
            ]),
            new IntField([
                'name' => 'iblock_property_id',
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
            'element' => new OneRelation('iblock_element_id', 'id', 'Element'),
            'property' => new OneRelation('iblock_property_id', 'id', 'Property'),
            'enumValues' => new ManyRelation('value_enum', 'id', 'PropertyEnum'),
        ];
    }
}

class Property extends ActiveRecord
{
    public static function getTableName(): string
    {
        return 'b_iblock_property';
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
            'values' => new ManyRelation('id', 'iblock_property_id', 'PropertyEnum'),
        ];
    }
}

class PropertyEnum extends ActiveRecord
{
    public static function getTableName(): string
    {
        return 'b_iblock_property_enum';
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
                'name' => 'iblock_property_id',
            ]),
            new TextField([
                'name' => 'value',
            ]),
        ];
    }

    public static function getRelations(): array
    {
        return [
            'property' => new OneRelation('iblock_property_id', 'id', 'Property'),
        ];
    }
}
