<?php

use PHPUnit\Framework\TestCase;
use tests\Post;
use jugger\ar\ActiveQuery;
use jugger\ar\relations\OneRelation;
use jugger\ar\relations\ManyRelation;
use jugger\ar\mapping\ForeignKey;
use jugger\ar\mapping\AssociationKey;


class RelationsTest extends TestCase
{
    public function testKeys()
    {
        $foreignKey = new ForeignKey('self_field', 'target_field', 'target_table');

        $this->assertEquals($foreignKey->getSelfField(), 'self_field');
        $this->assertEquals($foreignKey->getTargetField(), 'target_field');
        $this->assertEquals($foreignKey->getTargetTable(), 'target_table');

        $associationKey = new AssociationKey([
            ['field1', 'fieldTarget1', 'targetTable1'],
            ['field2', 'fieldTarget2', 'targetTable2'],
        ]);
        $associationKey->addKeyArray([
            'field3', 'fieldTarget3', 'targetTable3'
        ]);

        $i = 1;
        foreach ($associationKey->getKeys() as $key) {
            $this->assertEquals($key->getSelfField(), 'field'.$i);
            $this->assertEquals($key->getTargetField(), 'fieldTarget'.$i);
            $this->assertEquals($key->getTargetTable(), 'targetTable'.$i);
            $i++;
        }
    }

    /**
     * @depends testKeys
     */
    public function testQuery()
    {
        $foreignKey = new ForeignKey('self_field', 'target_field', 'target_table');
        $sql  = "SELECT `post`.`id`, `post`.`title`, `post`.`content` ";
        $sql .= "FROM `post` INNER JOIN target_table ON post.self_field = target_table.target_field ";

        $this->assertEquals(
            $sql,
            Post::find()->joinForeignKey($foreignKey)->build()
        );
    }
}
