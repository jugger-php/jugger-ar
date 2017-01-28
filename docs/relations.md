# Relations

Для удобства работы с таблицами и объектами, можно указать связи таблиц между собой. Всего можно указать 3 вида связи, но для начала определимся с исходными данными. Допустим БД имеет структуру:

```sql
CREATE TABLE section (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    name TEXT
);

CREATE TABLE element (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    name TEXT
);

CREATE TABLE property (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    name TEXT
);

CREATE TABLE section_element (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    id_element INT,
    id_section INT
)

CREATE TABLE element_property (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    id_element INT,
    id_property INT,
    value TEXT
);
```

Пояснения:
- `section` - раздел
- `element` - запись
- `property` - свойство записи
- `section_element` - привязка **записи** к **разделу**
- `element_property` - значение **свойства** для определенной **записи**

Классы для данных таблиц:
```php
class Section extends ActiveRecord
{
    public static function getTableName(): string
    {
        return 'section';
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
            'elements' => (new ManyRelation('id', 'id_section', 'SectionElement'))->next('id_element', 'id', 'Element'),
        ];
    }
}

class SectionElement extends ActiveRecord
{
    public static function getTableName(): string
    {
        return 'section_element';
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
                'name' => 'id_element',
            ]),
            new IntField([
                'name' => 'id_section',
            ]),
        ];
    }

    public static function getRelations(): array
    {
        return [
            'section' => new OneRelation('id_section', 'id', 'Section'),
            'element' => new OneRelation('id_element', 'id', 'Element'),
        ];
    }
}

class Element extends ActiveRecord
{
    public static function getTableName(): string
    {
        return 'element';
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
            'sections' => (new ManyRelation('id', 'id_element', 'SectionElement'))->next('id_section', 'id', 'Section'),
            'properties' => new ManyRelation('id', 'id_element', 'ElementProperty'),
        ];
    }
}

class ElementProperty extends ActiveRecord
{
    public static function getTableName(): string
    {
        return 'element_property';
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
                'name' => 'id_element',
            ]),
            new IntField([
                'name' => 'id_property',
            ]),
            new TextField([
                'name' => 'value',
            ]),
        ];
    }

    public static function getRelations(): array
    {
        return [
            'element' => new OneRelation('id_element', 'id', 'Element'),
            'property' => new OneRelation('id_property', 'id', 'Property'),
        ];
    }
}

class Property extends ActiveRecord
{
    public static function getTableName(): string
    {
        return 'property';
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
}
```

## Работа со связями

Всего существует 2 вида связи:
- `OneRelation` - один к одному
- `ManyRelation` - один ко многим

Также `ManyRelation` расширяется до `CrossRelation`, но обо всем по порядку.

## OneRelation

Связь один к одному. Данный тип связи присутствует в объектах `SectionElement` и `ElementProperty`. Рассмотрим первый вариант:
```php
public static function getRelations(): array
{
    return [
        'section' => new OneRelation('id_section', 'id', 'Section'),
        'element' => new OneRelation('id_element', 'id', 'Element'),
    ];
}
```

Код выше связывает объект `SectionElement` с разделами и записями: `Section` и `Element` соответственно. Всю информацию о связи класс `OneRelation` получает из конструктора:

```php
$relation = new OneRelation('поле текущего класса', 'поле связного класса', 'связный класс');

// для более удобного понимания запись можно интерпретировать как равенство:
// section_element.id_section = section.id
```

Допустим в базе у нас хранятся следующие данные:
```sql
INSERT INTO `section` VALUES(1, 'sec1');
INSERT INTO `element` VALUES(1, 'elem1');
INSERT INTO `section_element`(id_section, id_element) VALUES(1, 1);
```

Работа со связями:
```php
$row = SectionElement::findOne();
$section = $row->section;
$section->name;  // sec1

$element = $row->element;
$element->name;  // elem1
```

## ManyRelation

Связь один ко многим. Данный вид связи присутствует в объекте `Element` (не только в нем, но рассматривтаься будет элемент). Формируется объект `ManyRelation` по такой же схеме как и `OneRelation`:

```php
public static function getRelations(): array
{
    return [
        'properties' => new ManyRelation('id', 'id_element', 'ElementProperty'),
    ];
}
```

Допустим в базе у нас хранятся следующие данные:
```sql
INSERT INTO `element` VALUES(1, 'elem1');
INSERT INTO `property` VALUES(1, 'prop1');
INSERT INTO `property` VALUES(2, 'prop2');
INSERT INTO `element_property`(id_property, id_element, value)
VALUES(1, 1, 'value 1'), (2, 1, 'value 2'), (2, 1, 'value 3');
```

Работа со связями:
```php
$element = new Element::findOne();
$props = $element->properties;
$props[0]->value; // value 1
$props[1]->value; // value 2
$props[2]->value; // value 3
```

## CrossRelation

Связь много ко многим. Для организации данной связи используются промежуточные таблицы хранящие идентификаторы связных таблиц. Рассмотрим на примере связи `Section` <-> `SectionElement` <-> `Element`.

Класс `Section`:
```php
public static function getRelations(): array
{
    return [
        'elements' => (new ManyRelation('id', 'id_section', 'SectionElement'))->next('id_element', 'id', 'Element'),
    ];
}
```

Класс `Element`:
```php
public static function getRelations(): array
{
    return [
        'sections' => (new ManyRelation('id', 'id_element', 'SectionElement'))->next('id_section', 'id', 'Section'),
    ];
}
```

Как можно заметить, связь "много ко многим" строиться с помощью `ManyRelation`. Интерпретировать запись данной связи можно так:
```php
$relation = (new ManyRelation('поле текущего класса', 'связное поле промежуточного класса', 'промежуточный класс'))->next('связное поле промежуточного класса', 'поле целевого класса', 'целевой класс');

// немного псевдокода
// для элемента
element.id = section_element.id_element <===> section_element.id_section = section.id

// для раздела
section.id = section_element.id_section <===> section_element.id_element = element.id

// либо это можно интерпретировать так
[element].id -> id_element.[section_element].id_section <- id.[section]
```

**Внимание:** теоретически, цепочку связей можно продливать сколько угодно, однако если это требуется делать более 1-го раза, значит нужно задуматься о перестройке схемы БД.

Допустим в базе у нас хранятся следующие данные:
```sql
INSERT INTO `section` VALUES(1, 'sec1');
INSERT INTO `section` VALUES(2, 'sec2');
INSERT INTO `element` VALUES(1, 'elem1');
INSERT INTO `element` VALUES(2, 'elem2');
INSERT INTO `section_element`(id_section, id_element) VALUES(1, 1), (2, 1), (2, 2);
```

Работа со связями:
```php
$s1 = Section::findOne(1);
$s2 = Section::findOne(2);

$e1 = Element::findOne(1);
$e2 = Element::findOne(2);

// связи разделов
$s1->elements[0]->id == $e1->id;
$s2->elements[0]->id == $e1->id;
$s2->elements[1]->id == $e2->id;

// связи элементов
$e1->sections[0]->id == $s1->id;
$e1->sections[1]->id == $s2->id;
$e2->sections[0]->id == $s2->id;

// можно писать и так, но учтите что при такой записи будет сделано дополнительных 4 запроса
$e2 = $e1->sections[0]->elements[1]->sections[0]->elements[0];
```

## Поиск по связям

Связи позваляют не только получать связные строки, но также и производить поиск по ним:

```php
// список разделов, для элемента с именем 'elem1'
$sections1 = Section::find()
    ->byElements(['element.name' => 'elem1'])
    ->all();
// почти эквивалент
$sections2 = Element::findOne(['name' => 'elem1'])->sections;
```

Разница в этих двух записях только в количестве запросов: для `sections1` будет выполнен 1 запрос, для `sections2` - 2 запроса.

Далее представлены примеры поиска через связи:
```php
// поиск значений, для указанного свойства и элемента
ElementProperty::find()
    ->byElement([
        'element.name' => "название элемента",
    ])
    ->byProperty([
        'property.name' => "название свойства",
    ]);

// поиск элементов, для указанного раздела и имеющих ID больше 10
Element::find()
    ->bySections([
        'section.name' => 'sec1',
    ])
    ->where([
        '>element.id' => 10,
    ])
    ->all();
```

В качестве параметра, методы `by[relationName]` принимают список условий, такой же как в методе `Query->where`. [Подробнее про Query](https://github.com/jugger-php/jugger-db/blob/master/docs/README.md).
