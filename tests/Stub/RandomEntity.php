<?php declare(strict_types=1);

namespace Stefna\Collection\Tests\Stub;

final class RandomEntity
{
	public function __construct(
		public readonly string|int $value,
	) {}
}
