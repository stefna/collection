<?php declare(strict_types=1);

namespace Stefna\Collection;

/**
 * @template T of object
 * @extends \ArrayAccess<int, T>
 * @extends \IteratorAggregate<int, T>
 */
interface Collection extends \ArrayAccess, \Countable, \IteratorAggregate
{
	/**
	 * @phpstan-param T $element
	 */
	public function add(object $element): bool;

	/**
	 * @phpstan-param T $element
	 */
	public function remove(object $element): bool;

	/**
	 * @phpstan-param T $element
	 */
	public function contains(object $element): bool;

	/**
	 * @return class-string
	 */
	public function getType(): string;

	public function isEmpty(): bool;

	/**
	 * @return T[]
	 */
	public function toArray(): array;

	public function clear(): void;

	/**
	 * @return T|null
	 */
	public function first();
	/**
	 * @return T|null
	 */
	public function last();

	/**
	 * @param callable(T): bool $filter
	 * @return Collection<T>
	 */
	public function filter(callable $filter): Collection;

	/**
	 * @template TCallbackReturn of object
	 * @param callable(T):TCallbackReturn $callback
	 * @return Collection<TCallbackReturn>
	 */
	public function map(callable $callback): Collection;

	/**
	 * @param Collection<T> ...$collections
	 * @return Collection<T>
	 */
	public function merge(Collection ...$collections): Collection;
}
