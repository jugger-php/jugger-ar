# Active Record

## Создание

Допустим имеется таблица `post`:
```sql
CREATE TABLE `post` (
    `id` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
    `title` VARCHAR(100) NOT NULL,
    `content` TEXT
)
```

AR класс для таблицы будет выглядеть так:
```php
use jugger\db\ConnectionInterface;
use jugger\ar\ActiveRecord;
use jugger\ar\validator\PrimaryValidator;
use jugger\model\field\TextField;
use jugger\model\field\IntField;
use jugger\model\validator\RangeValidator;

class Post extends ActiveRecord
{
    /**
     * возвращает имя таблицы
     */
    public static function getTableName(): string
    {
        return 'post';
    }

    /**
     * возращает объект соединения с базой,
     * которая хранит таблицу
     */
    public static function getDb(): ConnectionInterface
    {
        return \Di::$c->db;
    }

    /**
     * возвращает структуру таблицы
     */
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
                    new RangeValidator(1, 100)
                ],
            ]),
            new TextField([
                'name' => 'content',
            ]),
        ];
    }
}
```

Метод `getSchema` ничем не отличается от аналогичного метода в классе `Model`, подробнее можно узнать в соответствующей документации. [Подробнее про Model](https://github.com/jugger-php/jugger-model/blob/master/docs/README.md).

Стоит обратить внимание на `PrimaryValidator` - это единственное отличие от полей и валидаторов `Model`. Данные валидатор показывает, что поле (*столбец*) является первичным ключем.

Метод `getDb` - в данном методе возвращается экземпляр базы данных `ConnectionInterface`. В данном примере используется контейнер зависимостей. [Подробнее про Di](https://github.com/jugger-php/jugger-di/blob/master/docs/README.md).

## Добавление и сохранение

`ActiveRecord` позволяет работать со строками таблиц БД, как с обычными объектами:
```php
// создание
$post = new Post();
$post->title = "Заголовок";
$post->content = "Содержание";

// после сохранения записи первичный ключ обновляется автоматически
$post->id; // NULL
$post->save();
$post->id; // 1

// удаление
$post->delete();

// после удаления объект не уничтожается, и вы можете использовать его далее
$post->save();
$post->id; // 2

// массовое обновление
$values = [
    'title' => "Новый заголовок"
];
$where = [
    '>id' => 10
];
Post::updateAll($values, $where);

// массовое удаление
Post::deleteAll($where);
```

Методы `save`, `updateAll`, `delete` и `deleteAll` возвращают количество затронутых строк.

Метод `updateAll` в качестве параметра `values` принимает массив ключами которого являются столбцы, а значениями - значения.

Методы `updateAll` и `deleteAll` в качестве параметра `where` принимают массив со списком условий. Оба методы являются оберткой над другими методами: `Command->update` и `Command->delete` соответственно. [Подробнее про Command](https://github.com/jugger-php/jugger-db/blob/master/docs/README.md).


## ActiveRecord extends Model

Объект `ActiveRecord` также является объектом `Model`, поэтому допустимы следующие конструкции:
```php
// присвоение в конструкторе
$post = new Post([
    'title' => "Заголовок",
    'content' => "Содержание",
]);

// получение/установка значений
$values = $post->getValues();
$post->setValues($values);

// грязная запись
$post->setValues([
    'test' => 'value',
    'title' => 'title',
]);
$post->test; // throw Exception
$post->title; // title


// ArrayAccess
$post->id = $post['id'];
```

Обратите внимание, что если искомого поля не существует, то выкидывается исключение (такое поведение тянется от модели, поэтому если вас такое поведение смутило, стоит ознакомиться со структурой `Model`). [Подробнее про Model](https://github.com/jugger-php/jugger-model/blob/master/docs/README.md).

## QueryBuilder

Помимо создания объектов, AR также позволяет и производить их поиск:
```php
// объект запроса
$query = Post::find();

// первый элемент в списке, объект Post
$post = Post::findOne();
$post->id == $query->one()->id; // true

// список всех элементов, возвращает список объектов Post
$posts = Post::findAll();
$posts == $query->all(); // true

// поиск записи по ID
$post = Post::findOne(123);
$post = $query->where(['id' => 123])->one();

// поиск записи по условию
$post = Post::findOne(["title" => "..."]);
$post = $query->where(["title" => "..."])->one();

// поиск всеъ записей по условию
$posts = Post::findAll([
    "title" => [
        'title1',
        'title2',
        '...'
    ]
]);
$posts = $query->where([
    "title" => [
        'title1',
        'title2',
        '...'
    ]
])->all();
```

Метод `find` возвращает объект `ActiveQuery`, который является наследником `Query` и содержит все его методы. [Подробнее про Query](https://github.com/jugger-php/jugger-db/blob/master/docs/README.md).

## Связи между объектами

Для удобства работы, также можно указывать связи между объектами. Об этом далее...

[RelationInterface: связи между объектами](relations.md)
