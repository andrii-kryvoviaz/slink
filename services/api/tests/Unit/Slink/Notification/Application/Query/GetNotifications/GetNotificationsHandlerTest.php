<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Notification\Application\Query\GetNotifications;

use Doctrine\ORM\Tools\Pagination\Paginator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Notification\Application\Query\GetNotifications\GetNotificationsHandler;
use Slink\Notification\Application\Query\GetNotifications\GetNotificationsQuery;
use Slink\Notification\Domain\Repository\NotificationRepositoryInterface;
use Slink\Notification\Infrastructure\ReadModel\View\NotificationView;
use Slink\Shared\Application\Http\Collection;

final class GetNotificationsHandlerTest extends TestCase {
  #[Test]
  public function itReturnsNotificationsCollection(): void {
    $notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
    $userId = 'user-123';

    $paginator = $this->createStub(Paginator::class);
    $paginator->method('getIterator')->willReturn(new \ArrayIterator([]));
    $paginator->method('count')->willReturn(0);

    $notificationRepository->expects($this->once())
      ->method('findByUserId')
      ->with($userId, 1, 20)
      ->willReturn($paginator);

    $handler = new GetNotificationsHandler($notificationRepository);

    $query = new GetNotificationsQuery();

    $result = $handler($query, $userId);

    $this->assertInstanceOf(Collection::class, $result);
  }

  #[Test]
  public function itPassesPaginationParametersToRepository(): void {
    $notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
    $userId = 'user-123';
    $page = 3;
    $limit = 50;

    $paginator = $this->createStub(Paginator::class);
    $paginator->method('getIterator')->willReturn(new \ArrayIterator([]));
    $paginator->method('count')->willReturn(0);

    $notificationRepository->expects($this->once())
      ->method('findByUserId')
      ->with($userId, $page, $limit)
      ->willReturn($paginator);

    $handler = new GetNotificationsHandler($notificationRepository);

    $query = new GetNotificationsQuery($page, $limit);

    $handler($query, $userId);
  }

  #[Test]
  public function itReturnsCorrectTotalCount(): void {
    $notificationRepository = $this->createStub(NotificationRepositoryInterface::class);
    $userId = 'user-123';
    $totalCount = 42;

    $paginator = $this->createStub(Paginator::class);
    $paginator->method('getIterator')->willReturn(new \ArrayIterator([]));
    $paginator->method('count')->willReturn($totalCount);

    $notificationRepository->method('findByUserId')->willReturn($paginator);

    $handler = new GetNotificationsHandler($notificationRepository);

    $query = new GetNotificationsQuery();

    $result = $handler($query, $userId);

    $this->assertEquals($totalCount, $result->total);
  }

  #[Test]
  public function itTransformsNotificationsToItems(): void {
    $notificationRepository = $this->createStub(NotificationRepositoryInterface::class);
    $userId = 'user-123';

    $notification1 = $this->createStub(NotificationView::class);
    $notification2 = $this->createStub(NotificationView::class);

    $paginator = $this->createStub(Paginator::class);
    $paginator->method('getIterator')->willReturn(new \ArrayIterator([$notification1, $notification2]));
    $paginator->method('count')->willReturn(2);

    $notificationRepository->method('findByUserId')->willReturn($paginator);

    $handler = new GetNotificationsHandler($notificationRepository);

    $query = new GetNotificationsQuery();

    $result = $handler($query, $userId);

    $this->assertCount(2, $result->data);
  }

  #[Test]
  public function itReturnsEmptyCollectionWhenNoNotifications(): void {
    $notificationRepository = $this->createStub(NotificationRepositoryInterface::class);
    $userId = 'user-123';

    $paginator = $this->createStub(Paginator::class);
    $paginator->method('getIterator')->willReturn(new \ArrayIterator([]));
    $paginator->method('count')->willReturn(0);

    $notificationRepository->method('findByUserId')->willReturn($paginator);

    $handler = new GetNotificationsHandler($notificationRepository);

    $query = new GetNotificationsQuery();

    $result = $handler($query, $userId);

    $this->assertCount(0, $result->data);
    $this->assertEquals(0, $result->total);
  }
}
