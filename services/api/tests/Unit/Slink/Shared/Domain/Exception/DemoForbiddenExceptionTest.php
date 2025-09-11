<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Domain\Exception;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\Exception\DemoForbiddenException;
use Slink\Shared\Domain\Exception\SpecificationException;
use LogicException;

final class DemoForbiddenExceptionTest extends TestCase {

  #[Test]
  public function itCreatesExceptionWithDefaultMessage(): void {
    $exception = new DemoForbiddenException();
    
    $this->assertEquals('This action is not allowed in demo mode', $exception->getMessage());
    $this->assertEquals('message', $exception->getProperty());
  }

  #[Test]
  public function itCreatesExceptionWithCustomMessage(): void {
    $customMessage = 'Custom demo mode restriction message';
    $exception = new DemoForbiddenException($customMessage);
    
    $this->assertEquals($customMessage, $exception->getMessage());
    $this->assertEquals('message', $exception->getProperty());
  }

  #[Test]
  public function itExtendsSpecificationException(): void {
    $exception = new DemoForbiddenException();
    
    $this->assertInstanceOf(SpecificationException::class, $exception);
    $this->assertInstanceOf(LogicException::class, $exception);
  }

  #[Test]
  public function itHasCorrectProperty(): void {
    $exception = new DemoForbiddenException();
    
    $this->assertEquals('message', $exception->getProperty());
  }
}
