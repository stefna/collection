<?php declare(strict_types=1);

namespace Stefna\Collection\Tests;

use PHPUnit\Framework\TestCase;
use Stefna\Collection\AbstractMapCollection;
use Stefna\Collection\Exception\CollectionMismatchException;
use Stefna\Collection\GenericListCollection;
use Stefna\Collection\GenericMapCollection;
use Stefna\Collection\Tests\Stub\ExtraEntity;
use Stefna\Collection\Tests\Stub\RandomEntity;

final class GenericMapCollectionTest extends TestCase
{
	public function testAddEntity(): void
	{
		$collection = new GenericMapCollection(RandomEntity::class);

		$this->assertCount(0, $collection);

		$collection['key1'] = new RandomEntity('1');
		$collection->add('key2', new RandomEntity('2'));

		$this->assertCount(2, $collection);
	}

	public function testAddEntityWithWrongType(): void
	{
		$collection = new GenericMapCollection(RandomEntity::class);

		$this->expectException(\TypeError::class);

		// @phpstan-ignore-next-line
		$collection->add('key', new ExtraEntity('1'));
	}

	public function testArrayAccessWithWrongType(): void
	{
		$collection = new GenericMapCollection(RandomEntity::class);

		$this->expectException(\TypeError::class);

		// @phpstan-ignore-next-line
		$collection['key'] = new ExtraEntity('1');
	}

	public function testArrayAccessWithIntOffsetInvalidArgument(): void
	{
		$collection = new GenericMapCollection(RandomEntity::class);

		$this->expectException(\InvalidArgumentException::class);

		// @phpstan-ignore-next-line
		$collection[10] = new RandomEntity('1');
	}

	public function testReplaceEntityWithIntOffset(): void
	{
		$collection = new GenericMapCollection(RandomEntity::class);

		$entity1 = new RandomEntity('1');
		$entity2 = new RandomEntity('2');

		$collection->add('key1', $entity1);

		$this->assertSame($entity1, $collection['key1']);

		$collection['key1'] = $entity2;
		$this->assertNotSame($entity1, $collection['key1']);
		$this->assertSame($entity2, $collection['key1']);
	}

	public function testGetReturnsNullWhenNotFound(): void
	{
		$collection = new GenericMapCollection(RandomEntity::class, [
			'key1' => new RandomEntity('2'),
			'key2' => new RandomEntity('3'),
		]);

		$this->assertNull($collection['not-found']);
	}

	public function testArrayAccessUnset(): void
	{
		$collection = new GenericMapCollection(RandomEntity::class, [
			'key1' => new RandomEntity('2'),
			'key2' => new RandomEntity('3'),
		]);

		unset($collection['key1']);

		$this->assertCount(1, $collection);
	}

	public function testRemove(): void
	{
		$entity1 = new RandomEntity('2');
		$entity2 = new RandomEntity('3');
		$collection = new GenericMapCollection(RandomEntity::class, [
			'key1' => $entity1,
			'key2' => $entity2,
		]);

		$this->assertTrue($collection->remove($entity2));
	}

	public function testRemoveByObjectUnderMultipleKeys(): void
	{
		$entity1 = new RandomEntity('2');
		$entity2 = new RandomEntity('3');
		$collection = new GenericMapCollection(RandomEntity::class, [
			'key1' => $entity1,
			'key2' => $entity2,
			'key3' => $entity1,
		]);

		$this->assertTrue($collection->remove($entity1));

		$this->assertCount(1, $collection);
		$this->assertFalse(isset($collection['key3']));
		$this->assertFalse(isset($collection['key1']));
	}

	public function testRemoveByKey(): void
	{
		$entity1 = new RandomEntity('2');
		$entity2 = new RandomEntity('3');
		$collection = new GenericMapCollection(RandomEntity::class, [
			'key1' => $entity1,
			'key2' => $entity2,
		]);

		$this->assertTrue($collection->remove('key2'));
		$this->assertFalse(isset($collection['key2']));
	}

	public function testMerge(): void
	{
		$collection1 = new GenericMapCollection(RandomEntity::class, [
			'key2' => new RandomEntity('2'),
			'key4' => new RandomEntity('4'),
		]);
		$collection2 = new GenericMapCollection(RandomEntity::class, [
			'key3' => new RandomEntity('3'),
			'key4' => new RandomEntity('5'),
		]);

		$mergeCollection = $collection1->merge($collection2);

		$this->assertCount(2, $collection1);
		$this->assertCount(2, $collection2);
		$this->assertCount(3, $mergeCollection);

		$this->assertSame('5', $mergeCollection['key4']?->value);
	}

	public function testMergeInvalidType(): void
	{
		$collection1 = new GenericMapCollection(RandomEntity::class, [
			'key1' => new RandomEntity('2'),
		]);
		$collection2 = new GenericMapCollection(ExtraEntity::class, [
			'key2' => new ExtraEntity('3'),
		]);

		$this->expectException(CollectionMismatchException::class);

		// @phpstan-ignore-next-line
		$collection1->merge($collection2);
	}

	public function testMergeInvalidCollectionType(): void
	{
		$collection1 = new GenericMapCollection(RandomEntity::class, [
			'key1' => new RandomEntity('2'),
		]);
		$collection2 = new GenericListCollection(ExtraEntity::class);

		$this->expectException(CollectionMismatchException::class);

		// @phpstan-ignore-next-line
		$collection1->merge($collection2);
	}

	public function testMap(): void
	{
		$value = '2';
		$collection1 = new GenericMapCollection(RandomEntity::class, [
			'key' => new RandomEntity($value),
		]);

		$newCollection = $collection1->map(fn (string $key, RandomEntity $entity) => new ExtraEntity(
			(string)$entity->value,
		));

		$this->assertNotSame($collection1, $newCollection);
		$this->assertSame(ExtraEntity::class, $newCollection->getType());
		$this->assertCount(1, $newCollection);
		$this->assertInstanceOf(ExtraEntity::class, $newCollection->last());
		$this->assertSame($value, $newCollection->first()?->value);
	}

	public function testFilter(): void
	{
		$collection1 = new GenericMapCollection(RandomEntity::class, [
			'key1' => new RandomEntity(1),
			'key2' => new RandomEntity(2),
			'key3' => new RandomEntity(3),
			'key4' => new RandomEntity(4),
			'key5' => new RandomEntity(5),
		]);

		$newCollection =  $collection1->filter(fn (string $key, RandomEntity $e) => $e->value >= 3);

		$this->assertFalse($newCollection->isEmpty());
		$this->assertCount(3, $newCollection);
		$this->assertSame(RandomEntity::class, $newCollection->getType());
	}

	public function testFilterByKey(): void
	{
		$collection1 = new GenericMapCollection(RandomEntity::class, [
			'key1' => new RandomEntity(1),
			'fKey2' => new RandomEntity(2),
			'fKey3' => new RandomEntity(3),
			'key4' => new RandomEntity(4),
			'key5' => new RandomEntity(5),
		]);

		$newCollection =  $collection1->filter(fn (string $key) => str_starts_with($key, 'fKey'));

		$this->assertFalse($newCollection->isEmpty());
		$this->assertCount(2, $newCollection);
		$this->assertSame(RandomEntity::class, $newCollection->getType());
		foreach ($newCollection as $key => $value) {
			$this->assertStringStartsWith('fKey', $key);
		}
	}

	public function testToList(): void
	{
		$collection1 = new GenericMapCollection(RandomEntity::class, [
			'key1' => new RandomEntity(1),
			'key2' => new RandomEntity(2),
			'key3' => new RandomEntity(3),
			'key4' => new RandomEntity(4),
			'key5' => new RandomEntity(5),
		]);

		$list = $collection1->toList();

		$this->assertSame(RandomEntity::class, $list->getType());

		$this->assertCount($collection1->count(), $list);
	}

	public function testGetFirstOnEmptyCollection(): void
	{
		$collection = new GenericMapCollection(RandomEntity::class);

		$this->assertTrue($collection->isEmpty());
		$this->assertNull($collection->first());
	}

	public function testGetLastOnEmptyCollection(): void
	{
		$collection = new GenericMapCollection(RandomEntity::class);

		$this->assertTrue($collection->isEmpty());
		$this->assertNull($collection->last());
	}

	public function testContains(): void
	{
		$entity = new RandomEntity('random');
		$collection = new GenericMapCollection(RandomEntity::class);
		$collection['key'] = $entity;

		$this->assertTrue($collection->contains('key'));
		$this->assertTrue($collection->contains($entity));
		$this->assertFalse($collection->contains('not-found'));
	}

	public function testIndexBy(): void
	{
		$collection = new GenericMapCollection(ExtraEntity::class, [
			'random1' => new ExtraEntity('1', 'index1'),
			'random2' => new ExtraEntity('2', 'index2'),
			'random3' => new ExtraEntity('3', 'index3'),
			'random4' => new ExtraEntity('4', 'index4'),
			'random5' => new ExtraEntity('5', 'index5'),
		]);

		$mapCollection = $collection->indexBy(fn (ExtraEntity $e) => $e->value2);

		$index = 1;
		foreach ($mapCollection as $key => $value) {
			$this->assertSame('index' . $index, $key);
			$this->assertInstanceOf(ExtraEntity::class, $value);
			$index++;
		}
	}
}
