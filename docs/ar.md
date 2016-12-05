# Active Record

Active record (AR) — шаблон проектирования приложений, является способом доступа к данным.
Каждый класс является отражением одной таблицы (представления), каждый экземпляр - отражением одной строки базы данных.
AR содержит в себе рутинные CRUD (Create Read Update Delete) операции и облегчает процесс разработки.

## Создание и генерация класса

Допустим имеется таблица:
```php
CREATE TABLE `user` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(100) NOT NULL,
    `password` VARCHAR(100) DEFAULT NULL,
)
```

Класс AR будет выглядеть следующим образом:
```php
use jugger\ar\ActiveRecord;
use jugger\ar\field\IntegerField;
use jugger\ar\field\TextField;

class User extends ActiveRecord
{
    public static function tableName()
    {
        return 'b_iblock_section_element';
    }

    public static function getFields()
    {
        return [
            new IntegerField([
                'column' => 'id',
                'primary' => true,
                'autoIncrement' => true,
            ]),
            new TextField([
                'column' => 'username',
                'length' => 100,
            ]),
            new TextField([
                'column' => 'password',
                'length' => 100,
                'default' => null,
            ]),
        ];
    }
}
```

Каждый столбец описывается соответствующих классом типа данных. [Подробнее]().

Для автоматизации и упрощения процесса создания классов, существует специальный класс генератор.
Для класса выше, генерация будет выглядить так:

```php
use jugger\ar\ActiveRecordGenerator;

// генерация без пространства имен (но заполнить придется вручную)
ActiveRecordGenerator::buildClass('user');
// можно также сразу указать пространство имен
ActiveRecordGenerator::buildClass('user', 'site\user');
```

Также можно указывать связи с другими сущностями. [Подробнее]().

## Создание и поиск записей

Для создания новой записи достаточно просто создать новый экземпляр класса:
```php
$user = new User();
$user->username = 'irpsv';
$user->password = '123456';
$user->save();
```
После вызова метода `save` будет выполнен непосредственно запрос к БД.

Для поиска и выборки записей используются методы `find`:
```php
// SELECT * FROM `user` WHERE `username` = 'irpsv' LIMIT 1
User::findOne([
    'username' => 'irpsv'
]);
// SELECT * FROM `user` WHERE (`username` = 'irpsv') OR (`password` LIKE '123456%')
User::findAll([
    'or',
    ['username' => 'irpsv'],
    ['%password' => '123546%'],
]);
// ActiveQuery
User::find();
```

Метод `find` возвращает объект запроса `ActiveQuery`, который позволяет изменить запрос перед выполнением.
[Подробнее]().

## Удаление записей

Удалить запись можно двумя способами:
```php
// удаление одной записи
User::findOne(['id' => 1])->delete();
// удаление всех записей удовлетворяющих условию
User::deleteAll(['id' => 1]);
```

## Active Query

Active Query (AQ) - это надстройка (дочерний класс) над классом `Query`, который является построителем запросов.
AQ является дополненым построителем запросов, который возвращает объекты сущности, а не массивы и позволяет использовать каскадные запросы к связным таблицам (об этом далее).

Пример работы с AQ:
```php
// получаем объект ActiveQuery
$query = User::find();
$query->build(); // === SELECT * FROM `user`

// получаем объекты User
$user = $query->one(); // === User::findOne();
$users = $query->all(); // === User::findAll();

// в остальном, это обычный Query
$query->where([
        '%name' => '123'
    ])
    ->orderBy([
        'id' => 'ASC'
    ])
    ->one(); // === SELECT * FROM `user` WHERE `name` LIKE '123' ORDER BY `id` ASC
```

## Связи сущностей

Для сущностей, также можно указать их связи с другими сущностями.
Для примера добавим таблицу `attribute`, которая будет иметь вид:
```php
CREATE TABLE `attribute` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `id_user` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `value` VARCHAR(100) NOT NULL,
)
```

Класс AR будет выглядеть следующим образом:
```php
use jugger\ar\ActiveRecord;
use jugger\ar\field\IntegerField;
use jugger\ar\field\TextField;

class Attribute extends ActiveRecord
{
    public static function tableName()
    {
        return 'b_iblock_section_element';
    }

    public static function getFields()
    {
        return [
            new IntegerField([
                'column' => 'id',
                'primary' => true,
                'autoIncrement' => true,
            ]),
            new IntegerField([
                'column' => 'id_user',
            ]),
            new TextField([
                'column' => 'name',
                'length' => 100,
            ]),
            new TextField([
                'column' => 'value',
                'length' => 100,
            ]),
        ];
    }

    public static function getRelations()
    {
        return [
            'user' => [
                'class' => 'User',
                'relation' => ['id_user' => 'id'],
            ],
        ];
    }
}
```

Для класса `User` также можно добавить информацию о связи:
```php
class User extends ActiveRecord
{
    // ...

    public static function getRelations()
    {
        return [
            'attributes' => [
                'class' => 'Attribute',
                'relation' => ['id' => 'id_user'],
                'many' => true,
            ],
        ];
    }
}
```

Информация о связи формируется следующим образом:
```php
'имя свойства' => [
    'class' => 'псевдоним\класса',
    'relation' => ['столбец в текущей таблице' => 'столбец в связной таблице'],
    'many' => true, // определяет тип связи 1:* или *:1
]
```

Когда указана связь, можно обратиться к экземпляру связной таблицы, как к обычному свойству:
```php
$attribute = Attribute::findOne(['id_user' => 1]);

// получаем объект пользователя
$user = $attribute->user; // === User::findOne(['id' => 1])

// получаем список атрибутов пользователя
// обратите внимание на возвращаемое значение
// если указан параметр 'many' в определении связи,
// то всегда будет возвращаться массив (пустой или с данными)
$user->attributes; // === Attribute::findAll(['id_user' => 1]);

```

Также, можно писать каскадные запросы при поиске записей:
```php
Attribute::find()
    ->by('user', [
        `%username` => '%abc%'
    ])
    ->one();

// Эквивалентный запрос:
// SELECT `attribute`.*
// FROM `attribute`
// INNER JOIN `user` ON `attribute`.`id_user` = `user`.`id`
// WHERE `username` LIKE '%abc%'
// LIMIT 1
```
