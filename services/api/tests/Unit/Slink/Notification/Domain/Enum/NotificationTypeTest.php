<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Notification\Domain\Enum;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Notification\Domain\Enum\NotificationType;

final class NotificationTypeTest extends TestCase {
  #[Test]
  public function itHasCommentType(): void {
    $type = NotificationType::COMMENT;

    $this->assertEquals('comment', $type->value);
  }

  #[Test]
  public function itHasCommentReplyType(): void {
    $type = NotificationType::COMMENT_REPLY;

    $this->assertEquals('comment_reply', $type->value);
  }

  #[Test]
  public function itHasAddedToFavoriteType(): void {
    $type = NotificationType::ADDED_TO_FAVORITE;

    $this->assertEquals('added_to_favorite', $type->value);
  }

  #[Test]
  public function itReturnsLabelForCommentType(): void {
    $this->assertEquals('New comment on your image', NotificationType::COMMENT->getLabel());
  }

  #[Test]
  public function itReturnsLabelForCommentReplyType(): void {
    $this->assertEquals('Someone replied to your comment', NotificationType::COMMENT_REPLY->getLabel());
  }

  #[Test]
  public function itReturnsLabelForAddedToFavoriteType(): void {
    $this->assertEquals('Your image was added to favorites', NotificationType::ADDED_TO_FAVORITE->getLabel());
  }

  #[Test]
  public function itCreatesFromValue(): void {
    $type = NotificationType::from('comment');

    $this->assertEquals(NotificationType::COMMENT, $type);
  }

  #[Test]
  public function itThrowsExceptionForInvalidValue(): void {
    $this->expectException(\ValueError::class);

    NotificationType::from('invalid_type');
  }
}
