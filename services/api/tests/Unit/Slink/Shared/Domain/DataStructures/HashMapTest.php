<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Domain\DataStructures;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\DataStructures\HashMap;
use stdClass;

final class HashMapTest extends TestCase {

  #[Test]
  public function itChecksIfKeyExists(): void {
    $hashMap = new HashMap(['existing_key' => 'value']);

    $this->assertTrue($hashMap->has('existing_key'));
    $this->assertFalse($hashMap->has('non_existing_key'));
  }

  #[Test]
  public function itClearsAllValues(): void {
    $hashMap = new HashMap(['key1' => 'value1', 'key2' => 'value2']);

    $hashMap->clear();

    $this->assertEquals(0, $hashMap->count());
    $this->assertEquals([], $hashMap->toArray());
  }

  #[Test]
  public function itCreatesEmptyHashMap(): void {
    $hashMap = new HashMap();

    $this->assertEquals(0, $hashMap->count());
    $this->assertEquals([], $hashMap->toArray());
  }

  #[Test]
  public function itCreatesHashMapFromArray(): void {
    $array = ['name' => 'John', 'age' => 30];
    $hashMap = HashMap::fromArray($array);

    $this->assertEquals(2, $hashMap->count());
    $this->assertEquals($array, $hashMap->toArray());
  }

  #[Test]
  public function itCreatesHashMapWithInitialValues(): void {
    $initialValues = ['key1' => 'value1', 'key2' => 'value2'];
    $hashMap = new HashMap($initialValues);

    $this->assertEquals(2, $hashMap->count());
    $this->assertEquals($initialValues, $hashMap->toArray());
  }

  #[Test]
  public function itHandlesIntegerKeys(): void {
    $hashMap = new HashMap([0 => 'zero', 1 => 'one']);

    $hashMap->set('2', 'two');

    $this->assertTrue($hashMap->has('0'));
    $this->assertTrue($hashMap->has('2'));
    $this->assertEquals('zero', $hashMap->get('0'));
    $this->assertEquals('two', $hashMap->get('2'));
  }

  #[Test]
  public function itHandlesMixedValueTypes(): void {
    $hashMap = new HashMap();

    $hashMap->set('string', 'text');
    $hashMap->set('integer', 42);
    $hashMap->set('array', [1, 2, 3]);
    $hashMap->set('object', new stdClass());

    $this->assertEquals('text', $hashMap->get('string'));
    $this->assertEquals(42, $hashMap->get('integer'));
    $this->assertEquals([1, 2, 3], $hashMap->get('array'));
    $this->assertInstanceOf(stdClass::class, $hashMap->get('object'));
  }

  #[Test]
  public function itMergesOverwritingExistingKeys(): void {
    $hashMap1 = new HashMap(['a' => 1, 'b' => 2]);
    $hashMap2 = new HashMap(['b' => 99, 'c' => 3]);

    $hashMap1->merge($hashMap2);

    $expected = ['a' => 1, 'b' => 99, 'c' => 3];
    $this->assertEquals($expected, $hashMap1->toArray());
  }

  #[Test]
  public function itMergesWithAnotherHashMap(): void {
    $hashMap1 = new HashMap(['a' => 1, 'b' => 2]);
    $hashMap2 = new HashMap(['c' => 3, 'd' => 4]);

    $hashMap1->merge($hashMap2);

    $expected = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
    $this->assertEquals($expected, $hashMap1->toArray());
    $this->assertEquals(4, $hashMap1->count());
  }

  #[Test]
  public function itRemovesKeys(): void {
    $hashMap = new HashMap(['key1' => 'value1', 'key2' => 'value2']);

    $hashMap->remove('key1');

    $this->assertFalse($hashMap->has('key1'));
    $this->assertTrue($hashMap->has('key2'));
    $this->assertEquals(1, $hashMap->count());
  }

  #[Test]
  public function itReturnsKeys(): void {
    $hashMap = new HashMap(['a' => 1, 'b' => 2, 'c' => 3]);

    $keys = $hashMap->keys();

    $this->assertEquals(['a', 'b', 'c'], $keys);
  }

  #[Test]
  public function itReturnsValues(): void {
    $hashMap = new HashMap(['a' => 1, 'b' => 2, 'c' => 3]);

    $values = $hashMap->values();

    $this->assertEquals([1, 2, 3], $values);
  }

  #[Test]
  public function itSetsAndGetsValues(): void {
    $hashMap = new HashMap();

    $hashMap->set('username', 'john_doe');
    $hashMap->set('email', 'john@example.com');

    $this->assertEquals('john_doe', $hashMap->get('username'));
    $this->assertEquals('john@example.com', $hashMap->get('email'));
  }
}
