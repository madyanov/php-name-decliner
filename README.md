Склонятор имен по [правилам русского языка](http://www.imena.org/).

### Установка

```
$ composer require madyanov/name-decliner
```

### Использование

```php
$decliner = new Madyanov\Utils\NameDecliner('Роман');
print_r($decliner->applyMaleNameRules());
```

```
Array
(
    [2] => Романа
    [3] => Роману
    [4] => Романа
    [5] => Романом
    [6] => Романе
)
```

Доступ к нужным падежам осуществляется через константы `Madyanov\Utils\NameDecliner::CASE_*`.

### Тестирование

```
$ git clone https://github.com/madyanov/php-name-decliner
$ cd php-name-decliner
$ composer install
$ ./vendor/bin/phpunit
```

Тестовый набор имен находится в папке `tests/data`.
