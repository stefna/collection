<?php declare(strict_types=1);

namespace Stefna\Collection;

/**
 * @template T of object
 * @extends \ArrayAccess<string, T>
 * @extends \IteratorAggregate<string, T>
 */
interface AssocCollection extends \ArrayAccess, \Countable, \IteratorAggregate
{
	/**
	 * @phpstan-param T $element
	 */
	public function add(string $key, object $element): bool;

	/**
	 * @phpstan-param T|string $element
	 */
	public function remove(string|object $element): bool;

	/**
	 * @phpstan-param T|string $element
	 */
	public function contains(string|object $element): bool;

	/**
	 * @return class-string
	 */
	public function getType(): string;

	public function isEmpty(): bool;

	/**
	 * @return array<string, T>
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
	 * @param callable(string, T): bool $filter
	 * @return AssocCollection<T>
	 */
	public function filter(callable $filter): AssocCollection;

	/**
	 * @template TCallbackReturn of object
	 * @param callable(string, T):TCallbackReturn $callback
	 * @return AssocCollection<TCallbackReturn>
	 */
	public function map(callable $callback): AssocCollection;

	/**
	 * @param AssocCollection<T> ...$collections
	 * @return AssocCollection<T>
	 */
	public function merge(AssocCollection ...$collections): AssocCollection;
}
