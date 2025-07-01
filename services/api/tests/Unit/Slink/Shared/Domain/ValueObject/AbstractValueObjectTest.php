<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Domain\ValueObject;

use JsonSerializable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use ReflectionClass;
use Stringable;

final class AbstractValueObjectTest extends TestCase {

  #[Test]
  public function itComparesComplexValueObjects(): void {
    $valueObject1 = new ComplexValueObject('name', 25, 'email@test.com');
    $valueObject2 = new ComplexValueObject('name', 25, 'email@test.com');
    $valueObject3 = new ComplexValueObject('name', 26, 'email@test.com');

    $this->assertTrue($valueObject1->equals($valueObject2));
    $this->assertFalse($valueObject1->equals($valueObject3));
  }

  #[Test]
  public function itComparesDifferentValueObjects(): void {
    $valueObject1 = new ConcreteValueObject('value1');
    $valueObject2 = new ConcreteValueObject('value2');

    $this->assertFalse($valueObject1->equals($valueObject2));
  }

  #[Test]
  public function itComparesEqualValueObjects(): void {
    $valueObject1 = new ConcreteValueObject('same-value');
    $valueObject2 = new ConcreteValueObject('same-value');

    $this->assertTrue($valueObject1->equals($valueObject2));
  }

  #[Test]
  public function itComparesWithNull(): void {
    $valueObject = new ConcreteValueObject('test');

    $this->assertFalse($valueObject->equals(null));
  }

  #[Test]
  public function itConvertsToString(): void {
    $valueObject = new ConcreteValueObject('test');

    $result = (string)$valueObject;

    $this->assertJson($result);
  }

  #[Test]
  public function itHandlesEmptyValueObjects(): void {
    $valueObject1 = new EmptyValueObject();
    $valueObject2 = new EmptyValueObject();

    $this->assertTrue($valueObject1->equals($valueObject2));
  }

  #[Test]
  public function itHandlesNullProperties(): void {
    $valueObject1 = new ValueObjectWithNullables('test', null);
    $valueObject2 = new ValueObjectWithNullables('test', null);
    $valueObject3 = new ValueObjectWithNullables('test', 'value');

    $this->assertTrue($valueObject1->equals($valueObject2));
    $this->assertFalse($valueObject1->equals($valueObject3));
  }

  #[Test]
  public function itImplementsJsonSerializable(): void {
    $valueObject = new ConcreteValueObject('test');

    $this->assertInstanceOf(JsonSerializable::class, $valueObject);
  }

  #[Test]
  public function itImplementsStringable(): void {
    $valueObject = new ConcreteValueObject('test');

    $this->assertInstanceOf(Stringable::class, $valueObject);
  }

  #[Test]
  public function itIsAbstractClass(): void {
    $reflection = new ReflectionClass(AbstractValueObject::class);

    $this->assertTrue($reflection->isAbstract());
  }

  #[Test]
  public function itProvidesJsonStringRepresentation(): void {
    $valueObject = new ConcreteValueObject('test');

    $string = $valueObject->toString();

    $this->assertJson($string);
    $decoded = json_decode($string, true);
    $this->assertEquals(['value' => 'test'], $decoded);
  }

  #[Test]
  public function itSerializesToJson(): void {
    $valueObject = new ConcreteValueObject('test');

    $result = $valueObject->jsonSerialize();

    $this->assertArrayHasKey('value', $result);
    $this->assertEquals('test', $result['value']);
  }
}

final readonly class ConcreteValueObject extends AbstractValueObject {
  public function __construct(private string $value) {
  }

  public function getValue(): string {
    return $this->value;
  }
}

final readonly class ComplexValueObject extends AbstractValueObject {
  public function __construct(
    private string $name,
    private int    $age,
    private string $email
  ) {
  }

  public function getAge(): int {
    return $this->age;
  }

  public function getEmail(): string {
    return $this->email;
  }

  public function getName(): string {
    return $this->name;
  }
}

final readonly class EmptyValueObject extends AbstractValueObject {
}

final readonly class ValueObjectWithNullables extends AbstractValueObject {
  public function __construct(
    private string  $name,
    private ?string $optional
  ) {
  }

  public function getName(): string {
    return $this->name;
  }

  public function getOptional(): ?string {
    return $this->optional;
  }
}
