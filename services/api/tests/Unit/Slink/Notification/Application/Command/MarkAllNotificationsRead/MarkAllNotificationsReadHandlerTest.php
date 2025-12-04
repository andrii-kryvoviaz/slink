<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Notification\Application\Command\MarkAllNotificationsRead;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Notification\Application\Command\MarkAllNotificationsRead\MarkAllNotificationsReadCommand;
use Slink\Notification\Application\Command\MarkAllNotificationsRead\MarkAllNotificationsReadHandler;
use Slink\Notification\Domain\Repository\NotificationRepositoryInterface;

final class MarkAllNotificationsReadHandlerTest extends TestCase {
  #[Test]
  public function itMarksAllNotificationsAsRead(): void {
    $notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
    $userId = 'user-123';

    $notificationRepository->expects($this->once())
      ->method('countUnreadByUserId')
      ->with($userId)
      ->willReturn(5);

    $notificationRepository->expects($this->once())
      ->method('markAllAsReadByUserId')
      ->with($userId);

    $handler = new MarkAllNotificationsReadHandler($notificationRepository);

    $command = new MarkAllNotificationsReadCommand();

    $handler($command, $userId);
  }

  #[Test]
  public function itDoesNotMarkWhenNoUnreadNotifications(): void {
    $notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
    $userId = 'user-123';

    $notificationRepository->expects($this->once())
      ->method('countUnreadByUserId')
      ->with($userId)
      ->willReturn(0);

    $notificationRepository->expects($this->never())
      ->method('markAllAsReadByUserId');

    $handler = new MarkAllNotificationsReadHandler($notificationRepository);

    $command = new MarkAllNotificationsReadCommand();

    $handler($command, $userId);
  }

  #[Test]
  public function itChecksUnreadCountBeforeMarking(): void {
    $notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
    $userId = 'user-123';

    $notificationRepository->expects($this->once())
      ->method('countUnreadByUserId')
      ->willReturn(10);

    $notificationRepository->expects($this->once())
      ->method('markAllAsReadByUserId');

    $handler = new MarkAllNotificationsReadHandler($notificationRepository);

    $command = new MarkAllNotificationsReadCommand();

    $handler($command, $userId);
  }

  #[Test]
  public function itPassesCorrectUserIdToRepository(): void {
    $notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
    $userId = 'specific-user-id';

    $notificationRepository->expects($this->once())
      ->method('countUnreadByUserId')
      ->with($userId)
      ->willReturn(3);

    $notificationRepository->expects($this->once())
      ->method('markAllAsReadByUserId')
      ->with($userId);

    $handler = new MarkAllNotificationsReadHandler($notificationRepository);

    $command = new MarkAllNotificationsReadCommand();

    $handler($command, $userId);
  }
}
