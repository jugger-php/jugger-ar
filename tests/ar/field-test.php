<?php

use PHPUnit\Framework\TestCase;
use jugger\ar\field\BaseField;
use jugger\ar\field\TextField;
use jugger\ar\field\NumberField;
use jugger\ar\field\IntegerField;
use jugger\ar\field\BooleanField;
use jugger\ar\field\DatetimeField;

class AbstractField extends BaseField
{
    protected function prepareValue($value)
    {
        return $value;
    }
}

class FieldTest extends TestCase
{
    public function testBase()
    {
        $field = new AbstractField([
            'column' => 'name',
            'value' => '76te3gkhrnj',
            'unique' => true,
            'primary' => false,
            'autoIncrement' => true,
        ]);

        $this->assertEquals($field->getValue(), '76te3gkhrnj');
        $this->assertEquals($field->getColumn(), 'name');
        $this->assertEquals($field->unique, true);
        $this->assertEquals($field->primary, false);
        $this->assertEquals($field->autoIncrement, true);

        $field = new AbstractField(['column' => 'name']);
        $this->assertEquals($field->getValue(), null);

        $field->default = 'def value';
        $this->assertEquals($field->getValue(), 'def value');
    }

    public function testBoolean()
    {
        $f = new BooleanField(['column' => 'name']);
        $this->assertNull($f->getValue());

        $f->setValue(0);
        $this->assertFalse($f->getValue());

        $f->setValue(123);
        $this->assertTrue($f->getValue());

        $f->setValue(123.456);
        $this->assertTrue($f->getValue());

        $f->setValue('');
        $this->assertFalse($f->getValue());

        $f->setValue('123');
        $this->assertTrue($f->getValue());

        $f->setValue([]);
        $this->assertFalse($f->getValue());

        $f->setValue([1,2,3]);
        $this->assertTrue($f->getValue());

        $f->setValue(new stdClass);
        $this->assertTrue($f->getValue());
    }

    public function testDatetime()
    {
        /*
         * test formats
         */
        $time = time();
        $f = new DatetimeField([
            'column' => 'name',
            'value' => $time,
        ]);
        $this->assertEquals($f->getValue(), date('Y-m-d H:i:s', $time));

        $f = new DatetimeField([
            'column' => 'name',
            'value' => $time,
            'format' => DatetimeField::FORMAT_TIMESTAMP,
        ]);
        $this->assertEquals($f->getValue(), $time);

        $f = new DatetimeField([
            'column' => 'name',
            'format' => 'Y-m-d',
            'value' => '2016-12-12 12:00:46',
        ]);
        $this->assertNull($f->getValue());

        $f->setValue('2016-12-12');
        $this->assertEquals($f->getValue(), '2016-12-12');

        $f = new DatetimeField([
            'column' => 'name',
            'format' => 'd.m.Y',
            'value' => '2016-12-12',
        ]);

        $f->setValue(123);
        $this->assertEquals($f->getValue(), '01.01.1970');

        $f->setValue(1481817815);
        $this->assertEquals($f->getValue(), '15.12.2016');

        $f->setValue('1.2.2016');
        $this->assertEquals($f->getValue(), '01.02.2016');

        /*
         * test values
         */
         $f = new DatetimeField([
             'column' => 'name',
             'format' => 'Y-m-d',
         ]);

        $f->setValue(0);
        $this->assertEquals($f->getValue(), '1970-01-01');

        $f->setValue(123);
        $this->assertEquals($f->getValue(), '1970-01-01');

        $f->setValue(123.456);
        $this->assertEquals($f->getValue(), '1970-01-01');

        $f->setValue('');
        $this->assertNull($f->getValue());

        $f->setValue('123');
        $this->assertNull($f->getValue());

        $f->setValue([]);
        $this->assertNull($f->getValue());

        $f->setValue([1,2,3]);
        $this->assertNull($f->getValue());

        $f->setValue(new stdClass);
        $this->assertNull($f->getValue());
    }

    public function testInt()
    {
        $f = new IntegerField(['column' => 'name']);

        $f->setValue(0);
        $this->assertEquals($f->getValue(), 0);

        $f->setValue(123.456);
        $this->assertEquals($f->getValue(), 123);

        $f->setValue('123');
        $this->assertEquals($f->getValue(), 123);

        $f->setValue(0x539);
        $this->assertEquals($f->getValue(), 1337);

        $f->setValue(02471);
        $this->assertEquals($f->getValue(), 1337);

        $f->setValue(0b10100111001);
        $this->assertEquals($f->getValue(), 1337);

        $f->setValue(1337e0);
        $this->assertEquals($f->getValue(), 1337);

        $f->setValue('');
        $this->assertNull($f->getValue());

        $f->setValue('sdkjnsfkg89gf');
        $this->assertNull($f->getValue());

        $f->setValue([]);
        $this->assertNull($f->getValue());

        $f->setValue([1,2,3]);
        $this->assertNull($f->getValue());

        $f->setValue(new stdClass);
        $this->assertNull($f->getValue());

        $f->setValue(true);
        $this->assertNull($f->getValue());
    }

    public function testNumber()
    {
        $f = new NumberField(['column' => 'name']);

        $f->setValue(0);
        $this->assertEquals($f->getValue(), 0);

        $f->setValue(123);
        $this->assertEquals($f->getValue(), 123);

        $f->setValue(123.456);
        $this->assertEquals($f->getValue(), 123.456);

        $f->setValue('123');
        $this->assertEquals($f->getValue(), 123);

        $f->setValue('123.456');
        $this->assertEquals($f->getValue(), 123.456);

        $f->setValue('');
        $this->assertNull($f->getValue());

        $f->setValue('sdkjnsfkg89gf');
        $this->assertNull($f->getValue());

        $f->setValue([]);
        $this->assertNull($f->getValue());

        $f->setValue([1,2,3]);
        $this->assertNull($f->getValue());

        $f->setValue(new stdClass);
        $this->assertNull($f->getValue());

        $f->setValue(true);
        $this->assertNull($f->getValue());
    }

    public function testText()
    {
        /*
         * test length
         */
        $f = new TextField([
            'column' => 'name',
            'length' => 10,
        ]);

        $f->setValue("test");
        $this->assertEquals($f->getValue(), "test");

        try {
            $f->setValue("test string with many symbols");
        }
        catch (\Exception $ex) {
            // pass
        }
        $this->assertNotNull($ex);

        /*
         * test value
         */
         $f = new TextField(['column' => 'name']);

         $f->setValue(0);
         $this->assertEquals($f->getValue(), '0');

         $f->setValue(123);
         $this->assertEquals($f->getValue(), '123');

         $f->setValue(123.456);
         $this->assertEquals($f->getValue(), '123.456');

         $f->setValue('');
         $this->assertEquals($f->getValue(), '');

         $f->setValue('sdkjnsfkg89gf');
         $this->assertEquals($f->getValue(), 'sdkjnsfkg89gf');

         $f->setValue(true);
         $this->assertEquals($f->getValue(), 'true');

         $f->setValue([]);
         $this->assertNull($f->getValue());

         $f->setValue([1,2,3]);
         $this->assertNull($f->getValue());

         $f->setValue(new stdClass);
         $this->assertNull($f->getValue());
    }
}
