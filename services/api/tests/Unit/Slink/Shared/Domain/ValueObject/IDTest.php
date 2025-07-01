<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Domain\ValueObject;

use EventSauce\EventSourcing\AggregateRootId;
use JsonSerializable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Stringable;

final class IDTest extends TestCase {

  #[Test]
  public function itCreatesIdFromString(): void {
    $value = 'test-id-123';
    $id = ID::fromString($value);

    $this->assertEquals($value, $id->getValue());
    $this->assertEquals($value, $id->toString());
  }

  #[Test]
  public function itCreatesIdFromUnknownValue(): void {
    $id = ID::fromUnknown('test-value');

    $this->assertNotNull($id);
    $this->assertEquals('test-value', $id->getValue());
  }

  #[Test]
  public function itCreatesIdFromUnknownIntegerValue(): void {
    $id = ID::fromUnknown(123);

    $this->assertNotNull($id);
    $this->assertEquals('123', $id->getValue());
  }

  #[Test]
  public function itReturnsNullForNullUnknownValue(): void {
    $id = ID::fromUnknown(null);

    $this->assertNull($id);
  }

  #[Test]
  public function itGeneratesValidUuid(): void {
    $id = ID::generate();

    $this->assertMatchesRegularExpression(
      '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
      $id->getValue()
    );
  }

  #[Test]
  public function itGeneratesUniqueIds(): void {
    $id1 = ID::generate();
    $id2 = ID::generate();

    $this->assertNotEquals($id1->getValue(), $id2->getValue());
  }

  #[Test]
  public function itImplementsToStringMethod(): void {
    $value = 'test-id';
    $id = ID::fromString($value);

    $this->assertEquals($value, (string)$id);
  }

  #[Test]
  public function itImplementsJsonSerializable(): void {
    $value = 'test-id';
    $id = ID::fromString($value);

    $this->assertInstanceOf(JsonSerializable::class, $id);
  }

  #[Test]
  public function itImplementsStringable(): void {
    $value = 'test-id';
    $id = ID::fromString($value);

    $this->assertInstanceOf(Stringable::class, $id);
  }

  #[Test]
  public function itImplementsAggregateRootId(): void {
    $id = ID::fromString('test-id');

    $this->assertInstanceOf(AggregateRootId::class, $id);
  }

  #[Test]
  public function itComparesEqualIds(): void {
    $value = 'same-id';
    $id1 = ID::fromString($value);
    $id2 = ID::fromString($value);

    $this->assertTrue($id1->equals($id2));
  }

  #[Test]
  public function itComparesDifferentIds(): void {
    $id1 = ID::fromString('id-1');
    $id2 = ID::fromString('id-2');

    $this->assertFalse($id1->equals($id2));
  }

  #[Test]
  public function itComparesWithNull(): void {
    $id = ID::fromString('test-id');

    $this->assertFalse($id->equals(null));
  }

  #[Test]
  public function itHandlesEmptyString(): void {
    $id = ID::fromString('');

    $this->assertEquals('', $id->getValue());
    $this->assertEquals('', $id->toString());
  }

  #[Test]
  public function itHandlesSpecialCharacters(): void {
    $value = 'special-chars-!@#$%^&*()_+-=[]{}|;:,.<>?';
    $id = ID::fromString($value);

    $this->assertEquals($value, $id->getValue());
  }

  #[Test]
  public function itHandlesUnicodeCharacters(): void {
    $value = 'unicode-café-résumé-naïve';
    $id = ID::fromString($value);

    $this->assertEquals($value, $id->getValue());
  }
}
