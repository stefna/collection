<?php declare(strict_types=1);

namespace Stefna\Collection;

use Ds\Map;

/**
 * @template T of object
 * @extends AbstractMapCollection<T>
 */
final class GenericMapCollection extends AbstractMapCollection
{
	/**
	 * @param class-string<T> $type
	 * @param array<string, T>|Map<string, T> $data
	 */
	public function __construct(string $type, Map|array $data = [])
	{
		$this->collectionType = $type;
		parent::__construct($data);
	}
}
