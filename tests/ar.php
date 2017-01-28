<?php

use PHPUnit\Framework\TestCase;
use jugger\db\Query;
use jugger\ar\ActiveQuery;

include_once __DIR__.'/records.php';

class ActiveRecordTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        Di::$pool['default']->execute("DROP TABLE IF EXISTS `author`");
        Di::$pool['default']->execute("DROP TABLE IF EXISTS `post`");

        Di::$pool['default']->execute("
        CREATE TABLE `post` (
            `id` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
            `title` VARCHAR(100) NOT NULL,
            `content` TEXT);
        ");

        Di::$pool['default']->execute("
        CREATE TABLE `author` (
            `id` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
            `id_post` INTEGER NOT NULL,
            `name` TEXT);
        ");
    }

    public static function tearDownAfterClass()
    {
        Di::$pool['default']->execute("DROP TABLE IF EXISTS `author`");
        Di::$pool['default']->execute("DROP TABLE IF EXISTS `post`");
    }

    public function testBase()
    {
        $title = 'title test';
        $content = 'content test';

        $post = new Post();
        $post->title = $title;
        $post->save();
        $id = $post->id;

        $this->assertTrue($id > 0);

        $post->content = $content;
        $post->save();

        return $post;
    }

    /**
     * @depends testBase
     */
    public function testQuery($post)
    {
        $query = Post::find();

        $this->assertInstanceOf(Query::class, $query);
        $this->assertInstanceOf(ActiveQuery::class, $query);
        $this->assertEquals(
            $query->build(),
            "SELECT `post`.`id`, `post`.`title`, `post`.`content` FROM `post`"
        );

        $this->assertTrue($query->one() == Post::findOne());

        $row = Post::findOne();
        $this->assertEquals($row->id, $row->id);

        $row = Post::findOne($post->id);
        $this->assertEquals($row->id, $post->id);

        $row = Post::findOne([
            'title' => $post->title,
        ]);
        $this->assertEquals($row->id, $post->id);

        (new Post([
            'title' => 'post1',
        ]))->save();
        (new Post([
            'title' => 'post2',
        ]))->save();

        $rows = Post::findAll();
        $this->assertTrue(count($rows) == 3);
        $this->assertEquals($rows[0]->id, $post->id);

        $rows = Post::findAll([
            'title' => 'post1',
        ]);
        $this->assertTrue(count($rows) == 1);
        $this->assertEquals($rows[0]->title, 'post1');
    }

    /*
     * @depends testQuery
     */
    public function testUpdate()
    {
        $newTitle = "2873ryfbh3k5yg";

        Post::updateAll([
            'title' => $newTitle,
        ], [
            '!id' => null,
        ]);

        $posts = Post::findAll();
        $this->assertNotEmpty($posts);
        foreach ($posts as $post) {
            $this->assertEquals($post->title, $newTitle);
        }
    }

    /*
     * @depends testUpdate
     */
    public function testDelete()
    {
        Post::deleteAll('1=1');

        $this->assertEmpty(Post::findAll());
    }
}
