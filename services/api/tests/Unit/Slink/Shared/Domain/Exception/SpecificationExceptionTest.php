<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Domain\Exception;

use Exception;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Slink\Shared\Domain\Exception\SpecificationException;

final class SpecificationExceptionTest extends TestCase {

  #[Test]
  public function itCanBeUsedInInheritanceChain(): void {
    $exception = new ConcreteSpecificationException('Test message');

    $this->assertInstanceOf(SpecificationException::class, $exception);
    $this->assertInstanceOf(LogicException::class, $exception);
    $this->assertInstanceOf(Exception::class, $exception);
  }

  #[Test]
  public function itCreatesExceptionWithMessage(): void {
    $message = 'Specification not satisfied';
    $exception = new ConcreteSpecificationException($message);

    $this->assertEquals($message, $exception->getMessage());
  }

  #[Test]
  public function itExtendsLogicException(): void {
    $exception = new ConcreteSpecificationException('Test message');

    $this->assertInstanceOf(LogicException::class, $exception);
  }

  #[Test]
  public function itHandlesEmptyMessage(): void {
    $exception = new ConcreteSpecificationException('');

    $this->assertEquals('', $exception->getMessage());
  }

  #[Test]
  public function itHasAbstractGetPropertyMethod(): void {
    $reflection = new ReflectionClass(SpecificationException::class);
    $method = $reflection->getMethod('getProperty');

    $this->assertTrue($method->isAbstract());
  }

  #[Test]
  public function itHasNoCodeByDefault(): void {
    $exception = new ConcreteSpecificationException('Test message');

    $this->assertEquals(0, $exception->getCode());
  }

  #[Test]
  public function itHasNoPreviousExceptionByDefault(): void {
    $exception = new ConcreteSpecificationException('Test message');

    $this->assertNull($exception->getPrevious());
  }

  #[Test]
  public function itIsAbstractClass(): void {
    $reflection = new ReflectionClass(SpecificationException::class);

    $this->assertTrue($reflection->isAbstract());
  }

  #[Test]
  public function itRequiresGetPropertyImplementation(): void {
    $exception = new ConcreteSpecificationException('Test message');

    $this->assertEquals('test_property', $exception->getProperty());
  }
}

class ConcreteSpecificationException extends SpecificationException {
  public function getProperty(): string {
    return 'test_property';
  }
}
