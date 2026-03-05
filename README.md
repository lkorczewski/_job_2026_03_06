# SortedLinkedList

A simple sorted singly linked list implementation in PHP 8.3.

## Requirements

- PHP 8.3+

## Usage

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\SortedLinkedList;

$list = new SortedLinkedList([5, 1, 3, 3]);
$list->insert(2);

var_dump($list->toArray());      // [1, 2, 3, 3, 5]
var_dump($list->contains(3));    // true
var_dump($list->remove(3));      // true (removes first matching value)
var_dump(count($list));          // 4

foreach ($list as $value) {
    echo $value . PHP_EOL;
}
```

## Behavior

- The list is always kept sorted.
- Duplicates are allowed.
- A single list instance accepts one value type only:
  - all `int` values, or
  - all `string` values.
- Inserting a different type later throws `InvalidArgumentException`.
- `remove()` and `contains()` return `false` for mismatched types.

## Development

Run tests:

```bash
./vendor/bin/phpunit
```

Run static analysis:

```bash
./vendor/bin/phpstan analyse
```
