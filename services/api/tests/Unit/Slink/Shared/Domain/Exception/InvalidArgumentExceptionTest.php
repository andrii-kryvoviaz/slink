<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Domain\Exception;

use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\Exception\InvalidArgumentException;
use Slink\Shared\Domain\Exception\SpecificationException;

final class InvalidArgumentExceptionTest extends TestCase {

  #[Test]
  public function itCreatesExceptionWithMessageAndProperty(): void {
    $message = 'Invalid value provided';
    $property = 'username';

    $exception = new InvalidArgumentException($message, $property);

    $this->assertEquals($message, $exception->getMessage());
    $this->assertEquals($property, $exception->getProperty());
  }

  #[Test]
  public function itCreatesExceptionWithDefaultProperty(): void {
    $message = 'Invalid value provided';

    $exception = new InvalidArgumentException($message);

    $this->assertEquals($message, $exception->getMessage());
    $this->assertEquals('error', $exception->getProperty());
  }

  #[Test]
  public function itExtendsSpecificationException(): void {
    $exception = new InvalidArgumentException('Test message');

    $this->assertInstanceOf(SpecificationException::class, $exception);
  }

  #[Test]
  public function itIsALogicException(): void {
    $exception = new InvalidArgumentException('Test message');

    $this->assertInstanceOf(LogicException::class, $exception);
  }

  #[Test]
  public function itHandlesEmptyMessage(): void {
    $exception = new InvalidArgumentException('');

    $this->assertEquals('', $exception->getMessage());
    $this->assertEquals('error', $exception->getProperty());
  }

  #[Test]
  public function itHandlesEmptyProperty(): void {
    $exception = new InvalidArgumentException('Test message', '');

    $this->assertEquals('Test message', $exception->getMessage());
    $this->assertEquals('', $exception->getProperty());
  }

  #[Test]
  public function itHandlesSpecialCharactersInMessage(): void {
    $message = 'Special chars: !@#$%^&*()_+-=[]{}|;:,.<>?';
    $exception = new InvalidArgumentException($message);

    $this->assertEquals($message, $exception->getMessage());
  }

  #[Test]
  public function itHandlesSpecialCharactersInProperty(): void {
    $property = 'special_property-name.with.dots';
    $exception = new InvalidArgumentException('Test', $property);

    $this->assertEquals($property, $exception->getProperty());
  }

  #[Test]
  public function itHasNoCodeByDefault(): void {
    $exception = new InvalidArgumentException('Test message');

    $this->assertEquals(0, $exception->getCode());
  }

  #[Test]
  public function itHasNoPreviousExceptionByDefault(): void {
    $exception = new InvalidArgumentException('Test message');

    $this->assertNull($exception->getPrevious());
  }
}
