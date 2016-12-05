# Active Record

**ActiveRecord** - является "обёрткой" одной строки из БД или представления, включает в себя доступ к БД и логику обращения с данными.
Объект AR представляет собой класс, который наследует `ActiveRecord`.

## Пример реализации

Допустим имеется таблица:
```sql
CREATE TABLE `post` (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `title` VARCHAR(100) NOT NULL,
    `content` TEXT
)
```

Описывающий ее класс должен выглядеть так:
```php
```
