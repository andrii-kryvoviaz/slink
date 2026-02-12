<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Tag\Domain\Exception;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\Exception\SpecificationException;
use Slink\Tag\Domain\Exception\InvalidTagMoveException;

final class InvalidTagMoveExceptionTest extends TestCase {

  #[Test]
  public function itCreatesExceptionWithMessage(): void {
    $exception = new InvalidTagMoveException('Cannot move');

    $this->assertEquals('Cannot move', $exception->getMessage());
  }

  #[Test]
  public function itExtendsSpecificationException(): void {
    $exception = new InvalidTagMoveException('test');

    $this->assertInstanceOf(SpecificationException::class, $exception);
  }

  #[Test]
  public function itReturnsNewParentIdAsProperty(): void {
    $exception = new InvalidTagMoveException('test');

    $this->assertEquals('newParentId', $exception->getProperty());
  }
}
