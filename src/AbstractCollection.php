<?php declare(strict_types=1);

namespace Stefna\Collection;

use Ds\Vector;
use Stefna\Collection\Exception\CollectionMismatchException;
use Traversable;

/**
 * @template T of object
 * @implements Collection<T>
 */
abstract class AbstractCollection implements Collection
{
	/** @var Vector<T> */
	protected Vector $data;

	/**
	 * @param class-string<T> $collectionType
	 * @param array<T>|Vector<T> $data
	 */
	final public function __construct(
		protected string $collectionType,
		Vector|array $data = [],
	) {
		$this->data = new Vector();
		// Invoke offsetSet() for each value added; in this way, sub-classes
		// may provide additional logic about values added to the array object.
		foreach ($data as $value) {
			$this->add($value);
		}
	}

	public function getType(): string
	{
		return $this->collectionType;
	}

	/**
	 * @return Traversable<array-key, T>
	 */
	public function getIterator(): Traversable
	{
		return $this->data->getIterator();
	}

	/**
	 * @param array-key $offset The offset to check.
	 */
	public function offsetExists($offset): bool
	{
		return isset($this->data[$offset]);
	}

	/**
	 * @param int $offset The offset for which a value should be returned.
	 * @return T|null the value stored at the offset, or null if the offset does not exist.
	 */
	public function offsetGet($offset): ?object
	{
		try {
			return $this->data->get($offset);
		}
		catch (\OutOfRangeException) {
			return null;
		}
	}

	/**
	 * @param int|null $offset
	 * @param T $value The value to set at the given offset.
	 */
	public function offsetSet($offset, mixed $value): void
	{
		if (!$value instanceof $this->collectionType) {
			throw new \TypeError('Invalid type for collection. Expected: ' . $this->collectionType);
		}

		if ($offset === null) {
			$this->data->push($value);
		}
		elseif (is_int($offset)) {
			$this->data->set($offset, $value);
		}
		else {
			throw new \BadMethodCallException('Offset must be integer');
		}
	}

	/**
	 * Removes the given offset and its value from the array.
	 *
	 * @param array-key $offset The offset to remove from the array.
	 */
	public function offsetUnset($offset): void
	{
		$this->data->remove($offset);
	}

	public function count(): int
	{
		return $this->data->count();
	}

	public function clear(): void
	{
		$this->data = new Vector();
	}

	/**
	 * @inheritDoc
	 */
	public function toArray(): array
	{
		return $this->data->toArray();
	}

	public function isEmpty(): bool
	{
		return $this->data->isEmpty();
	}

	/**
	 * @phpstan-param T $element
	 */
	public function add(object $element): bool
	{
		if (!$element instanceof $this->collectionType) {
			throw new \InvalidArgumentException('Invalid type for collection. Expected: ' . $this->collectionType);
		}
		$this->data->push($element);
		return true;
	}

	public function remove(object $element): bool
	{
		$index = $this->data->find($element);
		if ($index === false) {
			return false;
		}
		$this->data->remove($index);
		return true;
	}

	public function contains(object $element): bool
	{
		return $this->data->contains($element);
	}

	public function first()
	{
		return $this->data->first();
	}

	public function last()
	{
		return $this->data->last();
	}

	public function filter(callable $filter): Collection
	{
		$newCollection = clone $this;
		$newCollection->data = $this->data->filter($filter);
		return $newCollection;
	}

	public function map(callable $callback): Collection
	{
		$newData = $this->data->map($callback);
		$collection = new static(get_class($newData->first()));
		// @phpstan-ignore-next-line
		$collection->data = $newData;
		return $collection;
	}

	public function merge(Collection ...$collections): Collection
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
