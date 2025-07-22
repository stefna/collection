<?php declare(strict_types=1);

namespace Stefna\Collection;

use Ds\Map;
use Ds\Pair;
use Stefna\Collection\Exception\CollectionMismatchException;
use Traversable;

/**
 * @template T of object
 * @implements MapCollection<T>
 */
abstract class AbstractMapCollection implements MapCollection
{
	/** @var class-string<T> */
	protected static string $defaultCollectionType;

	/** @var class-string<T> */
	protected string $collectionType;

	/** @var Map<string, T> */
	protected Map $data;

	/**
	 * @param array<string, T>|Map<string, T> $data
	 */
	public function __construct(
		Map|array $data = [],
	) {
		if (!isset($this->collectionType) && isset(static::$defaultCollectionType)) {
			$this->collectionType = static::$defaultCollectionType;
		}
		$this->data = new Map(); // @phpstan-ignore assign.propertyType
		// Invoke offsetSet() for each value added; in this way, sub-classes
		// may provide additional logic about values added to the array object.
		foreach ($data as $key => $value) {
			// @phpstan-ignore function.alreadyNarrowedType
			if (!is_string($key)) {
				throw new \BadMethodCallException('Must specify key');
			}

			$this->add($key, $value);
		}
	}

	public function getType(): string
	{
		return $this->collectionType;
	}

	/**
	 * @return Traversable<string, T>
	 */
	public function getIterator(): Traversable
	{
		return $this->data->getIterator();
	}

	/**
	 * @param array-key $offset The offset to check.
	 */
	public function offsetExists(mixed $offset): bool
	{
		return isset($this->data[$offset]);
	}

	/**
	 * @param string $offset The offset for which a value should be returned.
	 * @return T|null the value stored at the offset, or null if the offset does not exist.
	 */
	public function offsetGet(mixed $offset): ?object
	{
		// if null is removed "get" throws exception. PhpStorm is wrong default type isn't null it's undefined
		return $this->data->get($offset, null);
	}

	/**
	 * @param string $offset
	 * @param T $value The value to set at the given offset.
	 */
	public function offsetSet(mixed $offset, mixed $value): void
	{
		if (!$value instanceof $this->collectionType) {
			throw new \TypeError('Invalid type for collection. Expected: ' . $this->collectionType);
		}
		// @phpstan-ignore function.alreadyNarrowedType
		if (!is_string($offset)) {
			throw new \InvalidArgumentException('Offset must be of type string');
		}

		$this->data->put($offset, $value);
	}

	/**
	 * Removes the given offset and its value from the array.
	 *
	 * @param string $offset The offset to remove from the array.
	 */
	public function offsetUnset(mixed $offset): void
	{
		// if null is removed "remove" throws exception. PhpStorm is wrong default type isn't null it's undefined
		$this->data->remove($offset, null);
	}

	public function count(): int
	{
		return $this->data->count();
	}

	public function clear(): void
	{
		$this->data = new Map(); // @phpstan-ignore assign.propertyType
	}

	/**
	 * @inheritDoc
	 */
	public function toArray(): array
	{
		return $this->data->toArray();
	}

	/**
	 * @inheritDoc
	 */
	public function getArrayCopy(): array
	{
		return $this->data->toArray();
	}

	public function toList(): ListCollection
	{
		return new GenericListCollection($this->collectionType, $this);
	}

	public function isEmpty(): bool
	{
		return $this->data->isEmpty();
	}

	/**
	 * @phpstan-param T $element
	 */
	public function add(string $key, object $element): bool
	{
		if (!$element instanceof $this->collectionType) {
			throw new \TypeError('Invalid type for collection. Expected: ' . $this->collectionType);
		}
		$this->data->put($key, $element);
		return true;
	}

	public function remove(object|string $element): bool
	{
		if (is_string($element)) {
			return $this->data->remove($element, null) !== null;
		}
		$elementPairs = $this->data->filter(fn (mixed $key, object $value) => $value === $element)->pairs();
		/** @var Pair<string, T> $elementPair */
		foreach ($elementPairs as $elementPair) {
			$this->data->remove($elementPair->key, null);
		}
		return true;
	}

	public function contains(object|string $element): bool
	{
		if (is_string($element)) {
			return $this->data->hasKey($element);
		}
		return $this->data->hasValue($element);
	}

	public function first()
	{
		try {
			return $this->data->first()->value;
		}
		catch (\UnderflowException) {
			return null;
		}
	}

	public function last()
	{
		try {
			return $this->data->last()->value;
		}
		catch (\UnderflowException) {
			return null;
		}
	}

	public function filter(callable $filter): static
	{
		$newCollection = clone $this;
		$newCollection->data = $this->data->filter($filter);
		return $newCollection;
	}

	/**
	 * @template TCallbackReturn of object
	 * @param callable(string, T):TCallbackReturn $callback
	 * @return MapCollection<TCallbackReturn>
	 */
	public function map(callable $callback): MapCollection
	{
		if ($this->data->isEmpty()) {
			return new GenericMapCollection($this->collectionType); // @phpstan-ignore return.type
		}
		$newData = $this->data->map($callback);
		$collection = new GenericMapCollection(get_class($newData->first()->value));
		$collection->data = $newData;
		return $collection;
	}

	public function indexBy(callable $callback): MapCollection
	{
		$newCollection = new GenericMapCollection($this->getType());

		foreach ($this->data as $row) {
			$newCollection[$callback($row)] = $row;
		}

		/** @var GenericMapCollection<T> $newCollection */
		return $newCollection;
	}

	public function column(callable $callback): array
	{
		$column = [];
		foreach ($this->data as $row) {
			$column[] = $callback($row);
		}
		return $column;
	}

	public function merge(Collection ...$collections): static
	{
		$newCollection = $this->data;
		foreach ($collections as $index => $collection) {
			if (!$collection instanceof static) {
				throw new CollectionMismatchException(
					sprintf('Collection with index %d must be of type %s', $index, static::class)
				);
			}

			// When using generics (Collection.php etc),
			// we also need to make sure that the internal types match each other
			if ($collection->getType() !== $this->getType()) {
				throw new CollectionMismatchException(
					sprintf('Collection items in collection with index %d must be of type %s', $index, $this->getType())
				);
			}
			$newCollection = $newCollection->merge($collection->data);
		}
		$mergedCollection = clone $this;
		$mergedCollection->data = $newCollection;

		return $mergedCollection;
	}
}
