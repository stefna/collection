<?php declare(strict_types=1);

namespace Stefna\Collection\Tests;

use PHPUnit\Framework\TestCase;
use Stefna\Collection\ScalarMap;
use Stefna\Collection\ScalarMapTrait;

final class ScalarMapTest extends TestCase
{
	public function testGetStringIsString(): void
	{
		$map = $this->createMap();
		$value = $map->getString('testString');
		$this->assertIsString($value);
	}

	public function testGetStringWithDefaultValue(): void
	{
		$map = $this->createMap();
		$value = $map->getString('testStringNotFound', 'random');
		$this->assertIsString($value);
		$this->assertSame('random', $value);
	}

	public function testGetStringNotFound(): void
	{
		$map = $this->createMap();
		$this->assertNull($map->getString('testStringNotFound'));
	}

	public function testGetIntIsInt(): void
	{
		$map = $this->createMap();
		$value = $map->getInt('testInt');
		$this->assertIsInt($value);
	}

	public function testGetIntCastString(): void
	{
		$map = $this->createMap();
		$value = $map->getInt('testIntAsString');
		$this->assertIsInt($value);
	}

	public function testGetIntCastFloat(): void
	{
		$map = $this->createMap();
		$value = $map->getInt('testIntAsFloat');
		$this->assertIsInt($value);
	}

	public function testGetIntWithDefaultValue(): void
	{
		$map = $this->createMap();
		$value = $map->getInt('testIntNotFound', 42);
		$this->assertIsInt($value);
		$this->assertSame(42, $value);
	}

	public function testGetIntNotFound(): void
	{
		$map = $this->createMap();
		$this->assertNull($map->getInt('testIntNotFound'));
	}

	public function testGetFloatIsFloat(): void
	{
		$map = $this->createMap();
		$value = $map->getFloat('testFloat');
		$this->assertIsFloat($value);
	}

	public function testGetFloatWithDefaultValue(): void
	{
		$map = $this->createMap();
		$value = $map->getFloat('testFloatNotFound', 42.1);
		$this->assertIsFloat($value);
		$this->assertSame(42.1, $value);
	}

	public function testGetFloatNotFound(): void
	{
		$map = $this->createMap();
		$this->assertNull($map->getFloat('testFloatNotFound'));
	}

	/**
	 * @dataProvider boolKeys
	 */
	public function testGetBoolIsBool(string $key, mixed $expected): void
	{
		$map = $this->createMap();
		$value = $map->getBool($key);
		$this->assertIsBool($value);
		$this->assertSame($expected, $value);
	}

	public function testGetBoolWithDefaultValue(): void
	{
		$map = $this->createMap();
		$value = $map->getBool('testBoolNotFound', true);
		$this->assertIsBool($value);
		$this->assertSame(true, $value);
	}

	public function testGetBoolNotFound(): void
	{
		$map = $this->createMap();
		$this->assertNull($map->getFloat('testBoolNotFound'));
	}

	private function createMap(): ScalarMap
	{
		return new class implements ScalarMap {
			use ScalarMapTrait;

			public function getRawValue(string $key): mixed
			{
				return [
					'testString' => 'test',
					'testInt' => 21,
					'testIntAsString' => '12',
					'testIntAsFloat' => 24.4,
					'testFloat' => 43.5,
					'testBool' => false,
					'testBoolFromIntTrue' => 1,
					'testBoolFromIntFalse' => 0,
					'testBoolFromStringTrue' => '1',
					'testBoolFromStringFalse' => '0',
					'testBoolFromOnTrue' => 'on',
					'testBoolFromTrueString' => 'true',
					'testBoolFromOffFalse' => 'off',
					'testBoolFromFalseString' => 'false',
					'testBoolUnknownStringFalse' => 'random',
				][$key] ?? null;
			}
		};
	}

	public function boolKeys(): array
	{
		return [
			'testBool' => ['testBool', false],
			'testBoolFromIntTrue' => ['testBoolFromIntTrue', true],
			'testBoolFromIntFalse' => ['testBoolFromIntFalse', false],
			'testBoolFromStringTrue' => ['testBoolFromStringTrue', true],
			'testBoolFromStringFalse' => ['testBoolFromStringFalse', false],
			'testBoolFromOnTrue' => ['testBoolFromOnTrue', true],
			'testBoolFromTrueString' => ['testBoolFromTrueString', true],
			'testBoolFromOffFalse' => ['testBoolFromOffFalse', false],
			'testBoolFromFalseString' => ['testBoolFromFalseString', false],
			'testBoolUnknownStringFalse' => ['testBoolUnknownStringFalse', false],
		];
	}
}
