<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Comment\Domain\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Comment\Domain\Service\CommentEditPolicy;
use Slink\Shared\Domain\ValueObject\Date\DateTime;

final class CommentEditPolicyTest extends TestCase {
  #[Test]
  public function itAllowsEditWithin24Hours(): void {
    $createdAt = DateTime::now();

    $this->assertTrue(CommentEditPolicy::canEdit($createdAt));
  }

  #[Test]
  public function itAllowsEditAt23Hours(): void {
    $createdAt = DateTime::now()->modify('-23 hours');

    $this->assertTrue(CommentEditPolicy::canEdit($createdAt));
  }

  #[Test]
  public function itDisallowsEditAfter24Hours(): void {
    $createdAt = DateTime::now()->modify('-25 hours');

    $this->assertFalse(CommentEditPolicy::canEdit($createdAt));
  }

  #[Test]
  public function itReturnsCorrectEditDeadline(): void {
    $createdAt = DateTime::now();
    $deadline = CommentEditPolicy::getEditDeadline($createdAt);

    $expectedDeadline = $createdAt->modify('+24 hours');

    $this->assertEquals($expectedDeadline->toString(), $deadline->toString());
  }
}
