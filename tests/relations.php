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
            CREATE TABLE property (
                id INTEGER PRIMARY KEY AUTO_INCREMENT,
                name TEXT
            );

            CREATE TABLE property_enum (
                id INTEGER PRIMARY KEY AUTO_INCREMENT,
                id_property INT,
                value TEXT
            );

            CREATE TABLE element (
                id INTEGER PRIMARY KEY AUTO_INCREMENT,
                name TEXT
            );

            CREATE TABLE element_property (
                id INTEGER PRIMARY KEY AUTO_INCREMENT,
                id_element INT,
                id_property INT,
                value TEXT,
                value_enum INT
            );

            CREATE TABLE section (
                id INTEGER PRIMARY KEY AUTO_INCREMENT,
                name TEXT
            );

            CREATE TABLE section_element (
                id INTEGER PRIMARY KEY AUTO_INCREMENT,
                id_element INT,
                id_section INT
            )
        ";
        $db = Di::$pool['default'];
        $db->execute("DROP TABLE IF EXISTS property");
        $db->execute("DROP TABLE IF EXISTS property_enum");
        $db->execute("DROP TABLE IF EXISTS element");
        $db->execute("DROP TABLE IF EXISTS element_property");
        $db->execute("DROP TABLE IF EXISTS section");
        $db->execute("DROP TABLE IF EXISTS section_element");

        $sqls = explode(';', $sql);
        foreach ($sqls as $sql) {
            $db->execute($sql);
        }
    }

    public static function tearDownAfterClass()
    {
        $db = Di::$pool['default'];
        $db->execute("DROP TABLE IF EXISTS property");
        $db->execute("DROP TABLE IF EXISTS property_enum");
        $db->execute("DROP TABLE IF EXISTS element");
        $db->execute("DROP TABLE IF EXISTS element_property");
        $db->execute("DROP TABLE IF EXISTS section");
        $db->execute("DROP TABLE IF EXISTS section_element");
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
            'id_property' => $p1->id,
            'value' => 'enum value 1',
        ]);
        $pe1->save();

        $pe2 = new PropertyEnum([
            'id_property' => $p1->id,
            'value' => 'enum value 2',
        ]);
        $pe2->save();

        $pe3 = new PropertyEnum([
            'id_property' => $p1->id,
            'value' => 'enum value 3',
        ]);
        $pe3->save();
        // set values

        (new ElementProperty([
            'id_element' => $e1->id,
            'id_property' => $p1->id,
            'value' => 'test value 1',
        ]))->save();
        (new ElementProperty([
            'id_element' => $e2->id,
            'id_property' => $p2->id,
            'value' => 'test value 2',
        ]))->save();
        (new ElementProperty([
            'id_element' => $e2->id,
            'id_property' => $p1->id,
            'value_enum' => $pe1->id,
        ]))->save();
        (new ElementProperty([
            'id_element' => $e2->id,
            'id_property' => $p1->id,
            'value_enum' => $pe2->id,
        ]))->save();
        (new ElementProperty([
            'id_element' => $e2->id,
            'id_property' => $p1->id,
            'value_enum' => $pe3->id,
        ]))->save();

        (new SectionElement([
            'id_section' => $s1->id,
            'id_element' => $e1->id,
        ]))->save();
        (new SectionElement([
            'id_section' => $s2->id,
            'id_element' => $e1->id,
        ]))->save();
        (new SectionElement([
            'id_section' => $s2->id,
            'id_element' => $e2->id,
        ]))->save();

        (new PropertyEnum([
            'id_property' => $p2->id,
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
        $this->assertTrue(count($s2->elements) == 2);

        $this->assertEquals($s1->elements[0]->id, $e1->id);
        $this->assertEquals($s2->elements[0]->id, $e1->id);
        $this->assertEquals($s2->elements[1]->id, $e2->id);

        $sections = Section::find()
            ->distinct()
            ->byElements([
                'element.name' => 'elem1',
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
                'element.name' => 'elem2',
            ])
            ->all();
        $this->assertTrue(count($sections) == 1);
    }

    /**
     * @depends testCreate
     */
    public function testSectionElement($models)
    {
        list($e1, $e2, $s1, $s2, $p1, $p2, $pe1, $pe2, $pe3) = $models;

        $query = SectionElement::find()
            ->byElement([
                'element.name' => 'elem1'
            ])
            ->bySection([
                'section.name' => 'sec2',
            ]);

        $items = $query->all();
        $this->assertTrue(count($items) == 1);
        $this->assertEquals($items[0]->id_element, $e1->id);
        $this->assertEquals($items[0]->element->id, $e1->id);

        $this->assertEquals($items[0]->id_section, $s2->id);
        $this->assertEquals($items[0]->section->id, $s2->id);
    }

    /**
     * @depends testCreate
     */
    public function testElement($models)
    {
        list($e1, $e2, $s1, $s2, $p1, $p2, $pe1, $pe2, $pe3) = $models;

        // sections
        $this->assertTrue(count($e1->sections) == 2);
        $this->assertTrue(count($e2->sections) == 1);

        $this->assertEquals($e1->sections[0]->id, $s1->id);
        $this->assertEquals($e1->sections[1]->id, $s2->id);

        $this->assertEquals($e2->sections[0]->id, $s2->id);

        $elements = Element::find()
            ->distinct()
            ->bySections([
                'section.id' => [1,2,3],
            ])
            ->all();

        $this->assertTrue(count($elements) == 2);
        $this->assertEquals($elements[0]->id, $e1->id);
        $this->assertEquals($elements[1]->id, $e2->id);

        // properties
        $this->assertTrue(count($e1->properties) == 1);
        $this->assertTrue(count($e2->properties) == 4);

        $this->assertEquals($e1->properties[0]->id_property, $p1->id);
        $this->assertEquals($e1->properties[0]->value, 'test value 1');

        $this->assertEquals($e2->properties[0]->id_property, $p2->id);
        $this->assertEquals($e2->properties[0]->value, 'test value 2');

        $this->assertEquals($e2->properties[1]->id_property, $p1->id);
        $this->assertEquals($e2->properties[1]->value_enum, $pe1->id);

        $this->assertEquals($e2->properties[2]->id_property, $p1->id);
        $this->assertEquals($e2->properties[2]->value_enum, $pe2->id);

        $this->assertEquals($e2->properties[3]->id_property, $p1->id);
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
            $e1->properties[0]->id_property
        );
        $this->assertEquals(
            $p1->id,
            $e1->properties[0]->property->id
        );
        $this->assertEquals(
            $p1->id,
            $e1->properties[0]->property->values[0]->id_property
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
                'element.name' => $e2->name,
            ])
            ->byProperty([
                'property.id' => $p1->id,
            ])
            ->where([
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
