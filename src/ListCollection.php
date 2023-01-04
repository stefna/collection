<?php declare(strict_types=1);

namespace Stefna\Collection;

/**
 * @template T of object
 * @extends \ArrayAccess<int, T>
 * @extends Collection<T>
 */
interface ListCollection extends \ArrayAccess, Collection
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
	 * @param callable(T): bool $filter
	 */
	public function filter(callable $filter): static;

	/**
	 * @template TCallbackReturn of object
	 * @param callable(T):TCallbackReturn $callback
	 * @return ListCollection<TCallbackReturn>
	 */
	public function map(callable $callback): ListCollection;
}
