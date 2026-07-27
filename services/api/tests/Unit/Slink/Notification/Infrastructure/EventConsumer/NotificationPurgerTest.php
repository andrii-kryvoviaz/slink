<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Notification\Infrastructure\EventConsumer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Event\ImageWasDeleted;
use Slink\Notification\Domain\Repository\NotificationRepositoryInterface;
use Slink\Notification\Infrastructure\EventConsumer\NotificationPurger;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Event\UserWasPurged;

final class NotificationPurgerTest extends TestCase {

  #[Test]
  public function itDeletesNotificationsOfPurgedUser(): void {
    $userId = ID::generate();

    $notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
    $notificationRepository->expects($this->once())
      ->method('deleteByUserId')
      ->with($userId->toString());

    $consumer = new NotificationPurger($notificationRepository);

    $consumer->handleUserWasPurged(new UserWasPurged($userId));
  }

  #[Test]
  public function itDetachesPurgedUserFromNotificationsOfOthers(): void {
    $userId = ID::generate();

    $notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
    $notificationRepository->expects($this->once())
      ->method('detachActor')
      ->with($userId->toString());

    $consumer = new NotificationPurger($notificationRepository);

    $consumer->handleUserWasPurged(new UserWasPurged($userId));
  }

  #[Test]
  public function itDeletesNotificationsReferencingDeletedImage(): void {
    $imageId = ID::generate();

    $notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
    $notificationRepository->expects($this->once())
      ->method('deleteByImageId')
      ->with($imageId->toString());

    $consumer = new NotificationPurger($notificationRepository);

    $consumer->handleImageWasDeleted(new ImageWasDeleted($imageId, false));
  }
}
