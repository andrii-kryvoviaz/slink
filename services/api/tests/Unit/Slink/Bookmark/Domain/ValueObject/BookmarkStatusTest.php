<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Bookmark\Domain\ValueObject;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Bookmark\Domain\ValueObject\BookmarkStatus;

final class BookmarkStatusTest extends TestCase {
  #[Test]
  public function itCreatesBookmarkedStatus(): void {
    $status = new BookmarkStatus(true, 5);

    $this->assertTrue($status->isBookmarked);
    $this->assertEquals(5, $status->bookmarkCount);
  }

  #[Test]
  public function itCreatesNotBookmarkedStatus(): void {
    $status = new BookmarkStatus(false, 10);

    $this->assertFalse($status->isBookmarked);
    $this->assertEquals(10, $status->bookmarkCount);
  }

  #[Test]
  public function itCreatesStatusWithZeroCount(): void {
    $status = new BookmarkStatus(false, 0);

    $this->assertFalse($status->isBookmarked);
    $this->assertEquals(0, $status->bookmarkCount);
  }

  #[Test]
  public function itConvertsToPayload(): void {
    $status = new BookmarkStatus(true, 42);

    $payload = $status->toPayload();

    $this->assertArrayHasKey('isBookmarked', $payload);
    $this->assertArrayHasKey('bookmarkCount', $payload);
    $this->assertTrue($payload['isBookmarked']);
    $this->assertEquals(42, $payload['bookmarkCount']);
  }

  #[Test]
  public function itConvertsNotBookmarkedToPayload(): void {
    $status = new BookmarkStatus(false, 0);

    $payload = $status->toPayload();

    $this->assertFalse($payload['isBookmarked']);
    $this->assertEquals(0, $payload['bookmarkCount']);
  }
}
