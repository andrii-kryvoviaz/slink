<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Notification\Application\Query\GetUnreadCount;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Notification\Application\Query\GetUnreadCount\GetUnreadCountHandler;
use Slink\Notification\Application\Query\GetUnreadCount\GetUnreadCountQuery;
use Slink\Notification\Domain\Repository\NotificationRepositoryInterface;

final class GetUnreadCountHandlerTest extends TestCase {
  #[Test]
  public function itReturnsUnreadCount(): void {
    $notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
    $userId = 'user-123';
    $expectedCount = 5;

    $notificationRepository->expects($this->once())
      ->method('countUnreadByUserId')
      ->with($userId)
      ->willReturn($expectedCount);

    $handler = new GetUnreadCountHandler($notificationRepository);

    $query = new GetUnreadCountQuery();

    $result = $handler($query, $userId);

    $this->assertArrayHasKey('count', $result);
    $this->assertEquals($expectedCount, $result['count']);
  }

  #[Test]
  public function itReturnsZeroWhenNoUnreadNotifications(): void {
    $notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
    $userId = 'user-123';

    $notificationRepository->method('countUnreadByUserId')->willReturn(0);

    $handler = new GetUnreadCountHandler($notificationRepository);

    $query = new GetUnreadCountQuery();

    $result = $handler($query, $userId);

    $this->assertEquals(0, $result['count']);
  }

  #[Test]
  public function itPassesCorrectUserIdToRepository(): void {
    $notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
    $userId = 'specific-user-id';

    $notificationRepository->expects($this->once())
      ->method('countUnreadByUserId')
      ->with($userId)
      ->willReturn(10);

    $handler = new GetUnreadCountHandler($notificationRepository);

    $query = new GetUnreadCountQuery();

    $handler($query, $userId);
  }

  #[Test]
  public function itReturnsHighUnreadCount(): void {
    $notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
    $userId = 'user-123';
    $highCount = 999;

    $notificationRepository->method('countUnreadByUserId')->willReturn($highCount);

    $handler = new GetUnreadCountHandler($notificationRepository);

    $query = new GetUnreadCountQuery();

    $result = $handler($query, $userId);

    $this->assertEquals($highCount, $result['count']);
  }
}
