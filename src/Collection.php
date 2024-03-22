<?php declare(strict_types=1);

namespace Stefna\Collection;

/**
 * @template T of object
 * @extends \IteratorAggregate<array-key, T>
 */
interface Collection extends \Countable, \IteratorAggregate
{
	/**
	 * @return class-string
	 */
	public function getType(): string;

	public function isEmpty(): bool;

	/**
	 * @deprecated Use getArrayCopy
	 * @return T[]
	 */
	public function toArray(): array;

	/**
	 * @return T[]
	 */
	public function getArrayCopy(): array;

	public function clear(): void;

	/**
	 * @return T|null
	 */
	public function first();

	/**
	 * @return T|null
	 */
	public function last();

	public function filter(callable $filter): static;

	/**
	 * @template TCallbackReturn of object
	 * @param callable():TCallbackReturn $callback
	 * @return Collection<TCallbackReturn>
	 */
	public function map(callable $callback): Collection;

	/**
	 * @param Collection<T> ...$collections
	 */
	public function merge(Collection ...$collections): static;

	/**
	 * @param callable(T): string $callback
	 * @return Collection<T>
	 */
	public function indexBy(callable $callback): Collection;

	/**
	 * @template R
	 * @param callable(T): R $callback
	 * @return list<R>
	 */
	public function column(callable $callback): array;
}
