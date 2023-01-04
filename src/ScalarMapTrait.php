<?php declare(strict_types=1);

namespace Stefna\Collection;

trait ScalarMapTrait
{
	public function has(string $key): bool
	{
		return isset($this->getAllData()[$key]);
	}

	public function getString(string $key, ?string $default = null): ?string
	{
		$value = $this->getAllData()[$key] ?? $default;
		if (!is_scalar($value)) {
			return $default;
		}
		return (string)$value;
	}

	public function getInt(string $key, ?int $default = null): ?int
	{
		$value = $this->getAllData()[$key] ?? $default;
		if (is_numeric($value)) {
			return (int)$value;
		}
		return $default;
	}

	public function getFloat(string $key, ?float $default = null): ?float
	{
		$value = $this->getAllData()[$key] ?? $default;
		if (is_numeric($value)) {
			return (float)$value;
		}
		return $default;
	}

	public function getBool(string $key, ?bool $default = null): ?bool
	{
		$value = $this->getAllData()[$key] ?? $default;
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

	/**
	 * @return array<string, mixed>
	 */
	abstract public function getAllData(): array;
}
