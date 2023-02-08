# Collection

[![Build Status](https://github.com/stefna/collection/actions/workflows/continuous-integration.yml/badge.svg?branch=main)](https://github.com/stefna/collection/actions/workflows/continuous-integration.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/stefna/ds-collection.svg)](https://packagist.org/packages/stefna/ds-collection)
[![Software License](https://img.shields.io/github/license/stefna/collection.svg)](LICENSE)

This package is a lightweight config loader with type safety as the primary
corner stone.

## Requirements

PHP 8.2 or higher.

## Installation

```bash
composer require stefna/ds-collection
```

## Usage

### Generic implementations

We provide 2 generic implementations of the collections for convenience and internal usage

#### List:

```php
<?php

use Stefna\Collection\GenericListCollection;
use Stefna\Collection\GenericMapCollection;


/** @var GenericListCollection<ClassType> */
$collection = new GenericListCollection(ClassType::class);

$collection[] = new ClassType();
// or
$collection->add(new ClassType());

/** @var GenericMapCollection<ClassType> */
$newCollection = $collection->indexBy(fn (ClassType $o) => (string)$o->id);
```


#### Map

```php
<?php

use Stefna\Collection\GenericListCollection;
use Stefna\Collection\GenericMapCollection;


/** @var GenericMapCollection<ClassType> */
$collection = new GenericMapCollection(ClassType::class);

$collection['id'] = new ClassType();
// or
$collection->add('id', new ClassType());

foreach ($collection as $key => $object) {
	$key === string;
	$object === ClassType::class;
}

/** @var GenericListCollection<ClassType> */
$listCollection = $collection->toList();
foreach ($collection as $key => $object) {
	$key === int;
	$object === ClassType::class;
}

```

### Creating Typed Collections

It is preferable to subclass `AbstractListCollection` and `AbstractMapCollection`

```php
<?php

use Stefna\Collection\AbstractListCollection;

final class RowCollection extends AbstractListCollection
{
	protected static string $defaultCollectionType = ClassType::class;
}

$collection = new RowCollection();
$collection->getType() === ClassType::class;
```

## Contribute

We are always happy to receive bug/security reports and bug/security fixes

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
