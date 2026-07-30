<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Notification\Infrastructure\ReadModel\View;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Notification\Domain\Enum\NotificationType;
use Slink\Notification\Infrastructure\ReadModel\View\NotificationView;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class NotificationViewTest extends TestCase {
  private function createUser(): UserView {
    return new UserView(
      ID::generate()->toString(),
      Email::fromString('actor@example.com'),
      Username::fromString('actor'),
      DisplayName::fromString('Actor'),
      HashedPassword::encode('password123'),
      DateTime::now(),
      null,
      UserStatus::Active,
      null,
    );
  }

  private function createNotification(?UserView $actor): NotificationView {
    return new NotificationView(
      ID::generate()->toString(),
      $this->createUser(),
      NotificationType::COMMENT,
      $this->createStub(ImageView::class),
      null,
      $actor,
      DateTime::now(),
    );
  }

  #[Test]
  public function actorSummaryExposesOnlyIdAndDisplayName(): void {
    $notification = $this->createNotification($this->createUser());

    $summary = $notification->getActorSummary();

    self::assertNotNull($summary);
    self::assertSame(['id', 'displayName'], \array_keys($summary));
  }

  #[Test]
  public function actorSummaryIsNullWithoutActor(): void {
    $notification = $this->createNotification(null);

    self::assertNull($notification->getActorSummary());
  }
}
