<?php declare(strict_types=1);

namespace Stefna\Collection;

trait ScalarMapTrait
{
	public function has(string $key): bool
	{
		return $this->getRawValue($key) !== null;
	}

	public function getString(string $key, ?string $default = null): ?string
	{
		$value = $this->getRawValue($key) ?? $default;
		if (!is_scalar($value)) {
			return $default;
		}
		return (string)$value;
	}

	public function getInt(string $key, ?int $default = null): ?int
	{
		$value = $this->getRawValue($key) ?? $default;
		if (is_numeric($value)) {
			return (int)$value;
		}
		return $default;
	}

	public function getFloat(string $key, ?float $default = null): ?float
	{
		$value = $this->getRawValue($key) ?? $default;
		if (is_numeric($value)) {
			return (float)$value;
		}
		return $default;
	}

	public function getBool(string $key, ?bool $default = null): ?bool
	{
		$value = $this->getRawValue($key) ?? $default;
		if (is_bool($value) || $value === null) {
			return $value;
		}

		if (!is_scalar($value)) {
			return $default;
		}

		return in_array($value, [
			1,
			'1',
			'on',
			'true',
		], true);
	}

	abstract public function getRawValue(string $key): mixed;
}
