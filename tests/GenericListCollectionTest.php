<?php declare(strict_types=1);

namespace Stefna\Collection\Tests;

use PHPUnit\Framework\TestCase;
use Stefna\Collection\AbstractListCollection;
use Stefna\Collection\AbstractMapCollection;
use Stefna\Collection\Exception\CollectionMismatchException;
use Stefna\Collection\GenericListCollection;
use Stefna\Collection\Tests\Stub\ExtraEntity;
use Stefna\Collection\Tests\Stub\RandomEntity;

final class GenericListCollectionTest extends TestCase
{
	public function testAddEntity(): void
	{
		$collection = new GenericListCollection(RandomEntity::class);

		$this->assertCount(0, $collection);

		$collection[] = new RandomEntity('1');
		$collection->add(new RandomEntity('2'));

		$this->assertCount(2, $collection);
	}

	public function testAddEntityWithWrongType(): void
	{
		$collection = new GenericListCollection(RandomEntity::class);

		$this->expectException(\TypeError::class);

		// @phpstan-ignore-next-line
		$collection->add(new ExtraEntity('1'));
	}

	public function testArrayAccessWithWrongType(): void
	{
		$collection = new GenericListCollection(RandomEntity::class);

		$this->expectException(\TypeError::class);

		// @phpstan-ignore-next-line
		$collection[] = new ExtraEntity('1');
	}

	public function testArrayAccessWithStringOffset(): void
	{
		$collection = new GenericListCollection(RandomEntity::class);

		$this->expectException(\BadMethodCallException::class);

		// @phpstan-ignore-next-line
		$collection['random'] = new RandomEntity('1');
	}

	public function testArrayAccessWithIntOffsetOutOfRange(): void
	{
		$collection = new GenericListCollection(RandomEntity::class);

		$this->expectException(\OutOfRangeException::class);

		$collection[10] = new RandomEntity('1');
	}

	public function testReplaceEntityWithIntOffset(): void
	{
		$collection = new GenericListCollection(RandomEntity::class);

		$entity1 = new RandomEntity('1');
		$entity2 = new RandomEntity('2');

		$collection->add($entity1);

		$this->assertTrue(isset($collection[0]));

		$this->assertSame($entity1, $collection[0]);

		$collection[0] = $entity2;
		$this->assertNotSame($entity1, $collection[0]);
		$this->assertSame($entity2, $collection[0]);
	}

	public function testGetReturnsNullWhenNotFound(): void
	{
		$collection = new GenericListCollection(RandomEntity::class, [
			new RandomEntity('2'),
			new RandomEntity('3'),
		]);

		$this->assertNull($collection[3]);
	}

	public function testArrayAccessUnset(): void
	{
		$collection = new GenericListCollection(RandomEntity::class, [
			new RandomEntity('2'),
			new RandomEntity('3'),
		]);

		unset($collection[0]);

		$this->assertCount(1, $collection);
	}

	public function testRemove(): void
	{
		$entity1 = new RandomEntity('2');
		$entity2 = new RandomEntity('3');
		$collection = new GenericListCollection(RandomEntity::class, [
			$entity1,
			$entity2,
		]);

		$this->assertTrue($collection->remove($entity2));
		$this->assertFalse($collection->remove(new RandomEntity('3')));
	}

	public function testMerge(): void
	{
		$collection1 = new GenericListCollection(RandomEntity::class, [
			new RandomEntity('2'),
		]);
		$collection2 = new GenericListCollection(RandomEntity::class, [
			new RandomEntity('3'),
		]);

		$mergeCollection = $collection1->merge($collection2);

		$this->assertCount(1, $collection1);
		$this->assertCount(1, $collection2);
		$this->assertCount(2, $mergeCollection);
	}

	public function testMergeInvalidType(): void
	{
		$collection1 = new GenericListCollection(RandomEntity::class, [
			new RandomEntity('2'),
		]);
		$collection2 = new GenericListCollection(ExtraEntity::class, [
			new ExtraEntity('3'),
		]);

		$this->expectException(CollectionMismatchException::class);

		// @phpstan-ignore-next-line
		$collection1->merge($collection2);
	}

	public function testMergeInvalidCollectionType(): void
	{
		$collection1 = new GenericListCollection(RandomEntity::class, [
			new RandomEntity('2'),
		]);
		$collection2 = new class extends AbstractMapCollection {
			protected static string $defaultCollectionType = ExtraEntity::class;
		};

		$this->expectException(CollectionMismatchException::class);

		// @phpstan-ignore-next-line
		$collection1->merge($collection2);
	}

	public function testMap(): void
	{
		$value = '2';
		$collection1 = new GenericListCollection(RandomEntity::class, [
			new RandomEntity($value),
		]);

		$newCollection = $collection1->map(fn (RandomEntity $entity) => new ExtraEntity((string)$entity->value));

		$this->assertNotSame($collection1, $newCollection);
		$this->assertSame(ExtraEntity::class, $newCollection->getType());
		$this->assertCount(1, $newCollection);
		$this->assertInstanceOf(ExtraEntity::class, $newCollection->last());
		$this->assertSame($value, $newCollection->first()?->value);
	}

	public function testFilter(): void
	{
		$collection1 = new GenericListCollection(RandomEntity::class, [
			new RandomEntity(1),
			new RandomEntity(2),
			new RandomEntity(3),
			new RandomEntity(4),
			new RandomEntity(5),
		]);

		$newCollection =  $collection1->filter(fn (RandomEntity $e) => $e->value >= 3);

		$this->assertFalse($newCollection->isEmpty());
		$this->assertCount(3, $newCollection);
		$this->assertSame(RandomEntity::class, $newCollection->getType());
	}

	public function testGetFirstOnEmptyCollection(): void
	{
		$collection = new GenericListCollection(RandomEntity::class);

		$this->assertTrue($collection->isEmpty());
		$this->assertNull($collection->first());
	}

	public function testGetLastOnEmptyCollection(): void
	{
		$collection = new GenericListCollection(RandomEntity::class);

		$this->assertTrue($collection->isEmpty());
		$this->assertNull($collection->last());
	}

	public function testIndexBy(): void
	{
		$collection = new GenericListCollection(ExtraEntity::class, [
			new ExtraEntity('1', 'index1'),
			new ExtraEntity('2', 'index2'),
			new ExtraEntity('3', 'index3'),
			new ExtraEntity('4', 'index4'),
			new ExtraEntity('5', 'index5'),
		]);

		$mapCollection = $collection->indexBy(fn (ExtraEntity $e) => $e->value2);

		$index = 1;
		foreach ($mapCollection as $key => $value) {
			$this->assertSame('index' . $index, $key);
			$this->assertInstanceOf(ExtraEntity::class, $value);
			$index++;
		}
	}

	public function testDefaultCollectionType(): void
	{
		$collection = new class extends AbstractListCollection {
			protected static string $defaultCollectionType = ExtraEntity::class;
		};

		$this->assertSame(ExtraEntity::class, $collection->getType());
	}

	public function testSlice(): void
	{
		$collection1 = new GenericListCollection(RandomEntity::class, [
			new RandomEntity(1),
			new RandomEntity(2),
			new RandomEntity(3),
			new RandomEntity(4),
			new RandomEntity(5),
		]);

		$slicedCollection = $collection1->slice(1, 3);

		$this->assertCount(3, $slicedCollection);

		$expectedValues = [2,3,4];
		/** @var RandomEntity $value */
		foreach ($slicedCollection as $index => $value) {
			$this->assertSame($expectedValues[$index], $value->value);
		}
	}

	public function testColumn(): void
	{
		$collection1 = new GenericListCollection(RandomEntity::class, [
			new RandomEntity(1),
			new RandomEntity(2),
			new RandomEntity(3),
			new RandomEntity(4),
			new RandomEntity(5),
		]);

		$values = $collection1->column(fn (RandomEntity $r) => $r->value);

		$this->assertCount(5, $values);
		$this->assertIsList($values);
		$this->assertSame([1, 2, 3, 4, 5], $values);
	}

	public function testClear(): void
	{
		$element = new RandomEntity(1);
		$collection = new GenericListCollection(RandomEntity::class, [
			$element,
		]);

		$this->assertTrue($collection->contains($element));
		$this->assertCount(1, $collection);
		$collection->clear();
		$this->assertCount(0, $collection);
		$this->assertFalse($collection->contains($element));
	}
}
