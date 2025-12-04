<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Notification\Application\Command\MarkNotificationRead;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Notification\Application\Command\MarkNotificationRead\MarkNotificationReadCommand;
use Slink\Notification\Application\Command\MarkNotificationRead\MarkNotificationReadHandler;
use Slink\Notification\Domain\Enum\NotificationType;
use Slink\Notification\Domain\Notification;
use Slink\Notification\Domain\Repository\NotificationRepositoryInterface;
use Slink\Notification\Domain\Repository\NotificationStoreRepositoryInterface;
use Slink\Notification\Infrastructure\ReadModel\View\NotificationView;
use Slink\Shared\Domain\Exception\ForbiddenException;
use Slink\Shared\Domain\ValueObject\ID;

final class MarkNotificationReadHandlerTest extends TestCase {
  #[Test]
  public function itMarksNotificationAsRead(): void {
    $notificationId = ID::generate();
    $userId = ID::generate();

    $notificationStore = $this->createMock(NotificationStoreRepositoryInterface::class);
    $notificationRepository = $this->createMock(NotificationRepositoryInterface::class);

    $notification = Notification::create(
      $notificationId,
      $userId,
      NotificationType::COMMENT,
      ID::generate(),
    );

    $notificationView = $this->createMock(NotificationView::class);
    $notificationView->method('getUserId')->willReturn($userId->toString());

    $notificationRepository->expects($this->once())
      ->method('oneById')
      ->with($notificationId->toString())
      ->willReturn($notificationView);

    $notificationStore->expects($this->once())
      ->method('get')
      ->willReturn($notification);

    $notificationStore->expects($this->once())
      ->method('store')
      ->with($notification);

    $handler = new MarkNotificationReadHandler($notificationStore, $notificationRepository);

    $command = new MarkNotificationReadCommand();

    $handler($command, $notificationId->toString(), $userId->toString());
  }

  #[Test]
  public function itThrowsForbiddenExceptionWhenUserIsNotOwner(): void {
    $this->expectException(ForbiddenException::class);
    $this->expectExceptionMessage('You can only mark your own notifications as read');

    $notificationId = ID::generate();
    $ownerId = ID::generate();
    $differentUserId = ID::generate();

    $notificationStore = $this->createMock(NotificationStoreRepositoryInterface::class);
    $notificationRepository = $this->createMock(NotificationRepositoryInterface::class);

    $notificationView = $this->createMock(NotificationView::class);
    $notificationView->method('getUserId')->willReturn($ownerId->toString());

    $notificationRepository->method('oneById')->willReturn($notificationView);

    $handler = new MarkNotificationReadHandler($notificationStore, $notificationRepository);

    $command = new MarkNotificationReadCommand();

    $handler($command, $notificationId->toString(), $differentUserId->toString());
  }

  #[Test]
  public function itDoesNotCallStoreWhenUserIsNotOwner(): void {
    $notificationId = ID::generate();
    $ownerId = ID::generate();
    $differentUserId = ID::generate();

    $notificationStore = $this->createMock(NotificationStoreRepositoryInterface::class);
    $notificationRepository = $this->createMock(NotificationRepositoryInterface::class);

    $notificationView = $this->createMock(NotificationView::class);
    $notificationView->method('getUserId')->willReturn($ownerId->toString());

    $notificationRepository->method('oneById')->willReturn($notificationView);

    $notificationStore->expects($this->never())->method('get');
    $notificationStore->expects($this->never())->method('store');

    $handler = new MarkNotificationReadHandler($notificationStore, $notificationRepository);

    $command = new MarkNotificationReadCommand();

    try {
      $handler($command, $notificationId->toString(), $differentUserId->toString());
    } catch (ForbiddenException) {
    }
  }

  #[Test]
  public function itFetchesNotificationByCorrectId(): void {
    $notificationId = ID::generate();
    $userId = ID::generate();

    $notificationStore = $this->createMock(NotificationStoreRepositoryInterface::class);
    $notificationRepository = $this->createMock(NotificationRepositoryInterface::class);

    $notification = Notification::create(
      $notificationId,
      $userId,
      NotificationType::COMMENT,
      ID::generate(),
    );

    $notificationView = $this->createMock(NotificationView::class);
    $notificationView->method('getUserId')->willReturn($userId->toString());

    $notificationRepository->expects($this->once())
      ->method('oneById')
      ->with($notificationId->toString())
      ->willReturn($notificationView);

    $notificationStore->expects($this->once())
      ->method('get')
      ->with($this->callback(fn (ID $id) => $id->equals($notificationId)))
      ->willReturn($notification);

    $notificationStore->method('store');

    $handler = new MarkNotificationReadHandler($notificationStore, $notificationRepository);

    $command = new MarkNotificationReadCommand();

    $handler($command, $notificationId->toString(), $userId->toString());
  }
}
