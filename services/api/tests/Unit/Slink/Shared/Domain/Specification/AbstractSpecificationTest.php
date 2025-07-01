<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Domain\Specification;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Slink\Shared\Domain\Specification\AbstractSpecification;
use stdClass;

final class AbstractSpecificationTest extends TestCase {

  #[Test]
  public function itAcceptsMixedValueTypes(): void {
    $specification = new ConcreteSpecification();

    $this->assertTrue($specification->isSatisfiedBy('string'));
    $this->assertTrue($specification->isSatisfiedBy(123));
    $this->assertTrue($specification->isSatisfiedBy([]));
    $this->assertTrue($specification->isSatisfiedBy(new stdClass()));
    $this->assertTrue($specification->isSatisfiedBy(null));
  }

  #[Test]
  public function itAllowsConcreteImplementationToReturnFalse(): void {
    $specification = new FalseSpecification();

    $this->assertFalse($specification->isSatisfiedBy('any value'));
  }

  #[Test]
  public function itAllowsConcreteImplementationToReturnTrue(): void {
    $specification = new TrueSpecification();

    $this->assertTrue($specification->isSatisfiedBy('any value'));
  }

  #[Test]
  public function itCanBeExtendedWithConcreteImplementation(): void {
    $specification = new ConcreteSpecification();

    $this->assertInstanceOf(AbstractSpecification::class, $specification);
  }

  #[Test]
  public function itHasAbstractIsSatisfiedByMethod(): void {
    $reflection = new ReflectionClass(AbstractSpecification::class);
    $method = $reflection->getMethod('isSatisfiedBy');

    $this->assertTrue($method->isAbstract());
  }

  #[Test]
  public function itIsAbstractClass(): void {
    $reflection = new ReflectionClass(AbstractSpecification::class);

    $this->assertTrue($reflection->isAbstract());
  }

  #[Test]
  public function itSupportsComplexLogic(): void {
    $specification = new NumberSpecification();

    $this->assertTrue($specification->isSatisfiedBy(5));
    $this->assertTrue($specification->isSatisfiedBy(10));
    $this->assertFalse($specification->isSatisfiedBy(15));
    $this->assertFalse($specification->isSatisfiedBy('string'));
  }
}

class ConcreteSpecification extends AbstractSpecification {
  public function isSatisfiedBy(mixed $value): bool {
    return true;
  }
}

class TrueSpecification extends AbstractSpecification {
  public function isSatisfiedBy(mixed $value): bool {
    return true;
  }
}

class FalseSpecification extends AbstractSpecification {
  public function isSatisfiedBy(mixed $value): bool {
    return false;
  }
}

class NumberSpecification extends AbstractSpecification {
  public function isSatisfiedBy(mixed $value): bool {
    return is_int($value) && $value >= 1 && $value <= 10;
  }
}
