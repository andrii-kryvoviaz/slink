<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Bookmark\Domain\Exception;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Bookmark\Domain\Exception\SelfBookmarkException;
use Slink\Shared\Domain\Exception\SpecificationException;

final class SelfBookmarkExceptionTest extends TestCase {
  #[Test]
  public function itExtendsSpecificationException(): void {
    $exception = new SelfBookmarkException();

    $this->assertInstanceOf(SpecificationException::class, $exception);
  }

  #[Test]
  public function itHasCorrectMessage(): void {
    $exception = new SelfBookmarkException();

    $this->assertEquals('You cannot bookmark your own image', $exception->getMessage());
  }

  #[Test]
  public function itReturnsImageIdProperty(): void {
    $exception = new SelfBookmarkException();

    $this->assertEquals('imageId', $exception->getProperty());
  }
}
