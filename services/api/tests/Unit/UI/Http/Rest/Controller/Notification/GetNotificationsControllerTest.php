<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Notification;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Notification\Application\Query\GetNotifications\GetNotificationsQuery;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryBusInterface;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\HandlerArgumentsStamp;
use UI\Http\Rest\Controller\Notification\GetNotificationsController;
use UI\Http\Rest\Response\ApiResponse;

final class GetNotificationsControllerTest extends TestCase {
  #[Test]
  public function itReturnsNotificationsSuccessfully(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $user = $this->createMock(JwtUser::class);
    $collection = new Collection(1, 20, 0, []);

    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn('user-123');

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->callback(function ($envelope) {
        return $envelope instanceof Envelope
          && $envelope->getMessage() instanceof GetNotificationsQuery;
      }))
      ->willReturn($collection);

    $controller = new GetNotificationsController();
    $controller->setQueryBus($queryBus);

    $response = $controller($user);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
  }

  #[Test]
  public function itPassesUserIdToQuery(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $user = $this->createMock(JwtUser::class);
    $userId = 'specific-user-id';
    $collection = new Collection(0, 20, 0, []);

    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn($userId);

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->callback(function (Envelope $envelope) use ($userId) {
        $message = $envelope->getMessage();
        if (!$message instanceof GetNotificationsQuery) {
          return false;
        }
        $stamp = $envelope->last(HandlerArgumentsStamp::class);
        if (!$stamp instanceof HandlerArgumentsStamp) {
          return false;
        }
        $context = $stamp->getAdditionalArguments();
        return ($context['userId'] ?? null) === $userId;
      }))
      ->willReturn($collection);

    $controller = new GetNotificationsController();
    $controller->setQueryBus($queryBus);

    $controller($user);
  }

  #[Test]
  public function itHandlesPaginationParameters(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $user = $this->createStub(JwtUser::class);
    $page = 3;
    $limit = 10;
    $collection = new Collection(50, $limit, ($page - 1) * $limit, []);

    $user->method('getIdentifier')->willReturn('user-123');

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->callback(function (Envelope $envelope) use ($page, $limit) {
        $message = $envelope->getMessage();
        if (!$message instanceof GetNotificationsQuery) {
          return false;
        }
        return $message->getPage() === $page && $message->getLimit() === $limit;
      }))
      ->willReturn($collection);

    $controller = new GetNotificationsController();
    $controller->setQueryBus($queryBus);

    $controller($user, $page, $limit);
  }

  #[Test]
  public function itUsesDefaultPaginationValues(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $user = $this->createStub(JwtUser::class);
    $collection = new Collection(0, 20, 0, []);

    $user->method('getIdentifier')->willReturn('user-123');

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->callback(function (Envelope $envelope) {
        $message = $envelope->getMessage();
        if (!$message instanceof GetNotificationsQuery) {
          return false;
        }
        return $message->getPage() === 1 && $message->getLimit() === 20;
      }))
      ->willReturn($collection);

    $controller = new GetNotificationsController();
    $controller->setQueryBus($queryBus);

    $controller($user);
  }

  #[Test]
  public function itReturnsCollectionResponse(): void {
    $queryBus = $this->createStub(QueryBusInterface::class);
    $user = $this->createStub(JwtUser::class);
    $collection = new Collection(2, 20, 0, [
      Item::fromPayload('notification', ['id' => 'notif-1', 'type' => 'comment']),
      Item::fromPayload('notification', ['id' => 'notif-2', 'type' => 'comment_reply']),
    ]);

    $user->method('getIdentifier')->willReturn('user-123');
    $queryBus->method('ask')->willReturn($collection);

    $controller = new GetNotificationsController();
    $controller->setQueryBus($queryBus);

    $response = $controller($user);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
  }
}
