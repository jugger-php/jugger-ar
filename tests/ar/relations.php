<?php

use PHPUnit\Framework\TestCase;
use jugger\ar\relations\OneRelation;
use jugger\ar\relations\ManyRelation;
use jugger\ar\relations\CrossRelation;

include_once __DIR__.'/records.php';

class RelationsTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        $sql = "
            CREATE TABLE b_iblock_property (
                id INTEGER PRIMARY KEY AUTO_INCREMENT,
                name TEXT
            );

            CREATE TABLE b_iblock_property_enum (
                id INTEGER PRIMARY KEY AUTO_INCREMENT,
                iblock_property_id INT,
                value TEXT
            );

            CREATE TABLE b_iblock_element (
                id INTEGER PRIMARY KEY AUTO_INCREMENT,
                name TEXT
            );

            CREATE TABLE b_iblock_element_property (
                id INTEGER PRIMARY KEY AUTO_INCREMENT,
                iblock_element_id INT,
                iblock_property_id INT,
                value TEXT,
                value_enum INT
            );

            CREATE TABLE b_iblock_section (
                id INTEGER PRIMARY KEY AUTO_INCREMENT,
                name TEXT
            );

            CREATE TABLE b_iblock_section_element (
                id INTEGER PRIMARY KEY AUTO_INCREMENT,
                iblock_element_id INT,
                iblock_section_id INT
            )
        ";
        $db = Di::$pool['default'];
        $db->execute("DROP TABLE IF EXISTS b_iblock_property");
        $db->execute("DROP TABLE IF EXISTS b_iblock_property_enum");
        $db->execute("DROP TABLE IF EXISTS b_iblock_element");
        $db->execute("DROP TABLE IF EXISTS b_iblock_element_property");
        $db->execute("DROP TABLE IF EXISTS b_iblock_section");
        $db->execute("DROP TABLE IF EXISTS b_iblock_section_element");

        $sqls = explode(';', $sql);
        foreach ($sqls as $sql) {
            $db->execute($sql);
        }
    }

    public static function tearDownAfterClass()
    {
        $db = Di::$pool['default'];
        $db->execute("DROP TABLE IF EXISTS b_iblock_property");
        $db->execute("DROP TABLE IF EXISTS b_iblock_property_enum");
        $db->execute("DROP TABLE IF EXISTS b_iblock_element");
        $db->execute("DROP TABLE IF EXISTS b_iblock_element_property");
        $db->execute("DROP TABLE IF EXISTS b_iblock_section");
        $db->execute("DROP TABLE IF EXISTS b_iblock_section_element");
    }

    public function testCreate()
    {
        $e1 = new Element([
            'name' => 'elem1',
        ]);
        $e1->save();
        $e2 = new Element([
            'name' => 'elem2',
        ]);
        $e2->save();

        $s1 = new Section([
            'name' => 'sec1',
        ]);
        $s1->save();
        $s2 = new Section([
            'name' => 'sec2',
        ]);
        $s2->save();

        $p1 = new Property([
            'name' => 'sec1',
        ]);
        $p1->save();
        $p2 = new Property([
            'name' => 'sec2',
        ]);
        $p2->save();

        $pe1 = new PropertyEnum([
            'iblock_property_id' => $p1->id,
            'value' => 'enum value 1',
        ]);
        $pe1->save();

        $pe2 = new PropertyEnum([
            'iblock_property_id' => $p1->id,
            'value' => 'enum value 2',
        ]);
        $pe2->save();

        $pe3 = new PropertyEnum([
            'iblock_property_id' => $p1->id,
            'value' => 'enum value 3',
        ]);
        $pe3->save();
        // set values

        (new ElementProperty([
            'iblock_element_id' => $e1->id,
            'iblock_property_id' => $p1->id,
            'value' => 'test value 1',
        ]))->save();
        (new ElementProperty([
            'iblock_element_id' => $e2->id,
            'iblock_property_id' => $p2->id,
            'value' => 'test value 2',
        ]))->save();
        (new ElementProperty([
            'iblock_element_id' => $e2->id,
            'iblock_property_id' => $p1->id,
            'value_enum' => $pe1->id,
        ]))->save();
        (new ElementProperty([
            'iblock_element_id' => $e2->id,
            'iblock_property_id' => $p1->id,
            'value_enum' => $pe2->id,
        ]))->save();
        (new ElementProperty([
            'iblock_element_id' => $e2->id,
            'iblock_property_id' => $p1->id,
            'value_enum' => $pe3->id,
        ]))->save();

        (new SectionElement([
            'iblock_section_id' => $s1->id,
            'iblock_element_id' => $e1->id,
        ]))->save();
        (new SectionElement([
            'iblock_section_id' => $s2->id,
            'iblock_element_id' => $e1->id,
        ]))->save();

        (new PropertyEnum([
            'iblock_property_id' => $p2->id,
            'value' => 'enum value 5',
        ]))->save();

        return [$e1, $e2, $s1, $s2, $p1, $p2, $pe1, $pe2, $pe3];
    }

    /**
     * @depends testCreate
     */
    public function testSection($models)
    {
        list($e1, $e2, $s1, $s2, $p1, $p2, $pe1, $pe2, $pe3) = $models;

        $this->assertTrue(count($s1->elements) == 1);
        $this->assertTrue(count($s2->elements) == 1);

        $this->assertEquals($s1->elements[0]->id, $e1->id);
        $this->assertEquals($s2->elements[0]->id, $e1->id);

        $sections = Section::find()
            ->distinct()
            ->byElements([
                'b_iblock_element.name' => 'elem1',
            ])
            ->all();

        $this->assertTrue(count($sections) == 2);
        $this->assertEquals($sections[0]->id, $s1->id);
        $this->assertEquals($sections[1]->id, $s2->id);

        $sectionsEquivalent = Element::findOne(['name' => 'elem1'])->sections;
        foreach ($sections as $i => $sec) {
            $this->assertEquals($sec->id, $sectionsEquivalent[$i]->id);
        }

        $sections = Section::find()
            ->distinct()
            ->byElements([
                'b_iblock_element.name' => 'elem2',
            ])
            ->all();
        $this->assertEmpty($sections);
    }

    /**
     * @depends testCreate
     */
    public function testSectionElement($models)
    {
        list($e1, $e2, $s1, $s2, $p1, $p2, $pe1, $pe2, $pe3) = $models;

        $query = SectionElement::find()
            ->byElement([
                'b_iblock_element.name' => 'elem1'
            ])
            ->bySection([
                'b_iblock_section.name' => 'sec2',
            ]);

        $items = $query->all();
        $this->assertTrue(count($items) == 1);
        $this->assertEquals($items[0]->iblock_element_id, $e1->id);
        $this->assertEquals($items[0]->iblock_section_id, $s2->id);
    }

    /**
     * @depends testCreate
     */
    public function testElement($models)
    {
        list($e1, $e2, $s1, $s2, $p1, $p2, $pe1, $pe2, $pe3) = $models;

        // sections
        $this->assertTrue(count($e1->sections) == 2);
        $this->assertTrue(count($e2->sections) == 0);

        $this->assertEquals($e1->sections[0]->id, $s1->id);
        $this->assertEquals($e1->sections[1]->id, $s2->id);

        $elements = Element::find()
            ->distinct()
            ->bySections([
                'b_iblock_section.id' => [1,2,3],
            ])
            ->all();

        $this->assertTrue(count($elements) == 1);
        $this->assertEquals($elements[0]->id, $e1->id);

        // properties
        $this->assertTrue(count($e1->properties) == 1);
        $this->assertTrue(count($e2->properties) == 4);

        $this->assertEquals($e1->properties[0]->iblock_property_id, $p1->id);
        $this->assertEquals($e1->properties[0]->value, 'test value 1');

        $this->assertEquals($e2->properties[0]->iblock_property_id, $p2->id);
        $this->assertEquals($e2->properties[0]->value, 'test value 2');

        $this->assertEquals($e2->properties[1]->iblock_property_id, $p1->id);
        $this->assertEquals($e2->properties[1]->value_enum, $pe1->id);

        $this->assertEquals($e2->properties[2]->iblock_property_id, $p1->id);
        $this->assertEquals($e2->properties[2]->value_enum, $pe2->id);

        $this->assertEquals($e2->properties[3]->iblock_property_id, $p1->id);
        $this->assertEquals($e2->properties[3]->value_enum, $pe3->id);

        $elements = Element::find()
            ->byProperties([
                'value' => 'test value 2',
            ])
            ->all();

        $this->assertTrue(count($elements) == 1);
        $this->assertEquals($elements[0]->id, $e2->id);

        // WTF example
        $this->assertEquals(
            $p1->id,
            $e1->properties[0]->iblock_property_id
        );
        $this->assertEquals(
            $p1->id,
            $e1->properties[0]->property->id
        );
        $this->assertEquals(
            $p1->id,
            $e1->properties[0]->property->values[0]->iblock_property_id
        );
        $this->assertEquals(
            $p1->id,
            $e1->properties[0]->property->values[0]->property->id
        );

        $this->assertEquals(
            $s1->id,
            $e1->sections[0]->id
        );
        $this->assertEquals(
            $e1->id,
            $e1->sections[0]->elements[0]->id
        );
        $this->assertEquals(
            $p1->id,
            $e1->sections[0]->elements[0]->properties[0]->property->values[0]->property->id
        );
    }

    /**
     * @depends testCreate
     */
    public function testElementProperty($models)
    {
        list($e1, $e2, $s1, $s2, $p1, $p2, $pe1, $pe2, $pe3) = $models;

        $query = ElementProperty::find()
            ->byElement([
                'b_iblock_element.name' => $e2->name,
            ])
            ->byProperty([
                'b_iblock_property.id' => $p1->id,
            ])
            ->andWhere([
                'value' => null,
            ]);

        $items = $query->all();
        $this->assertTrue(count($items) == 3);
        $this->assertEquals($items[0]->value_enum, $pe1->id);
        $this->assertEquals($items[1]->value_enum, $pe2->id);
        $this->assertEquals($items[2]->value_enum, $pe3->id);
    }

    /**
     * @depends testCreate
     */
    public function testProperty($models)
    {
        list($e1, $e2, $s1, $s2, $p1, $p2, $pe1, $pe2, $pe3) = $models;

        $this->assertTrue(count($p1->values) == 3);
        $this->assertTrue(count($p2->values) == 1);

        $this->assertEquals($p1->values[0]->id, $pe1->id);
        $this->assertEquals($p1->values[1]->id, $pe2->id);
        $this->assertEquals($p1->values[2]->id, $pe3->id);

        $this->assertEquals($p2->values[0]->value, 'enum value 5');

        $props = Property::find()
            ->distinct()
            ->byValues([
                'or',
                '%value' => 'enum%2',
                '%value' => 'enum%3',
            ])
            ->all();

        $this->assertTrue(count($props) == 1);
        $this->assertEquals($props[0]->id, $p1->id);
    }

    /**
     * @depends testCreate
     */
    public function testPropertyEnum($models)
    {
        list($e1, $e2, $s1, $s2, $p1, $p2, $pe1, $pe2, $pe3) = $models;

        $this->assertEquals(
            $p1->id,
            $p1->values[0]->property->id
        );
    }
}
