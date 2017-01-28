<?php

use PHPUnit\Framework\TestCase;
use tests\Post;
use tests\Author;
use jugger\db\Query;
use jugger\db\ConnectionPool;
use jugger\ar\tools\ActiveRecordGenerator;

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

        $row = Post::findOne($id);
        $this->assertEquals($row->id, $id);
        $this->assertEquals($row->title, $title);
        $this->assertEquals($row->content, $content);

        $row2 = Post::findOne([
            'title' => $title,
        ]);
        $this->assertEquals($row2->id, $row->id);

        $firstRow = Post::findOne();
        $this->assertEquals($firstRow->id, $row->id);

        $row->delete();
        $this->assertNull(Post::findOne($id));
    }

    public function testQuery()
    {
        $query = Post::find();

        $this->assertInstanceOf(Query::class, $query);
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

        $anotherAuthor = new Author([
            'id_post' => 123,
            'name' => 'Another author',
        ]);
        $anotherAuthor->save();

        $this->assertTrue(count($post->authors) === 1);
        $this->assertEquals($author->id, $post->authors[0]->id);
        $this->assertEquals($author->post->id, $post->id);
    }

    /*
     * @depends testRelations
     */
    public function testRelationQuery()
    {
        $this->assertEquals(
            Author::find()
                ->by('post', [
                    'post.title' => 'new post',
                ])
                ->build(),
            "SELECT `author`.`id`, `author`.`id_post`, `author`.`name` FROM `author` INNER JOIN `post` ON `author`.`id_post` = `post`.`id`  WHERE `post`.`title` = 'new post'"
        );

        $author = Author::find()
            ->by('post', [
                'post.title' => 'new post',
            ])
            ->one();

        $this->assertNotNull($author);
        $this->assertEquals($author->name, 'Ivan Ivanov');

        $author2 = Author::find()
            ->byPost([
                'post.title' => 'new post',
            ])
            ->one();

        $this->assertEquals($author->id, $author2->id);

        $posts = Post::find()
            ->byAuthors([
                'author.id' => $author->id,
            ])
            ->all();

        $this->assertNotEmpty($posts);
        $this->assertEquals($posts[0]->authors[0]->id, $author->id);
    }

    /*
     * @depends testRelationQuery
     */
    public function testUpdate()
    {
        $newTitle = "2873ryfbh3k5yg";

        Post::updateAll([
            'title' => $newTitle,
        ], '1=1');

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
        Author::deleteAll('1=1');

        $this->assertEmpty(Post::findAll());
        $this->assertEmpty(Author::findAll());
    }
}
