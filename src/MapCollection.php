<?php declare(strict_types=1);

namespace Stefna\Collection;

/**
 * @template T of object
 * @extends \ArrayAccess<string, T>
 * @extends Collection<T>
 */
interface MapCollection extends \ArrayAccess, Collection
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
	 * @param callable(string, T): bool $filter
	 */
	public function filter(callable $filter): static;

	/**
	 * @template TCallbackReturn of object
	 * @param callable(string, T):TCallbackReturn $callback
	 * @return MapCollection<TCallbackReturn>
	 */
	public function map(callable $callback): MapCollection;

	/**
	 * @return ListCollection<T>
	 */
	public function toList(): ListCollection;

	/**
	 * @param callable(T): string $callback
	 * @return MapCollection<T>
	 */
	public function indexBy(callable $callback): MapCollection;
}
