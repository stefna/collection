<?php declare(strict_types=1);

namespace Stefna\Collection;

/**
 * @template T of object
 * @extends AbstractListCollection<T>
 */
final class GenericListCollection extends AbstractListCollection
{
	/**
	 * @param class-string<T> $type
	 * @param iterable<T> $data
	 */
	public function __construct(string $type, iterable $data = [])
	{
		$this->collectionType = $type;
		parent::__construct($data);
	}
}
