<?php

use PHPUnit\Framework\TestCase;
use tests\Post;
use tests\Author;
use jugger\db\ConnectionPool;
use jugger\ar\tools\ActiveRecordGenerator;

class ActiveRecordTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        $sqls = [];
        $sqls[] = "
        CREATE TABLE `post` (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            `title` VARCHAR(100) NOT NULL,
            `content` TEXT
        )
        ";

        $sqls[] = "
        CREATE TABLE `author` (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            `id_post` INTEGER NOT NULL,
            `name` TEXT
        )
        ";

        foreach ($sqls as $sql) {
            ConnectionPool::get('default')->execute($sql);
        }
    }

    public function testBase()
    {
        $title = 'title test';
        $content = 'content test';

        $post = new Post();
        $post->title = $title;
        $id = $post->save();

        $this->assertTrue($id > 0);

        $post->content = $content;
        $post->save();

        $row = Post::findOne($id);
        $this->assertEquals($row->id, $id);
        $this->assertEquals($row->title, $title);
        $this->assertEquals($row->content, $content);

        $row->delete();

        $this->assertNull(Post::findOne($id));
    }

    public function testQuery()
    {
        $query = Post::find();

        $this->assertInstanceOf(
            \jugger\db\Query::class,
            $query
        );
        $this->assertEquals(
            $query->build(),
            "SELECT `post`.`id`, `post`.`title`, `post`.`content` FROM `post`"
        );
    }

    /*
     * @depends testQuery
     */
    public function testRelations()
    {
        $post = new Post([
            'title' => 'new post',
            'content' => 'content',
        ]);
        $post->save();

        $author = new Author([
            'id_post' => $post->id,
            'name' => 'Ivan Ivanov',
        ]);
        $author->save();

        (new Author([
            'id_post' => 123,
            'name' => 'Another author',
        ]))->save();

        $this->assertTrue(count($post->authors) === 1);
        $this->assertEquals($author->id, $post->authors[0]->id);
        $this->assertEquals($author->post->id, $post->id);
    }
}
