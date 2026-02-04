<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Notification;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Notification\Application\Query\GetUnreadCount\GetUnreadCountQuery;
use Slink\Shared\Application\Query\QueryBusInterface;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\HandlerArgumentsStamp;
use UI\Http\Rest\Controller\Notification\GetUnreadCountController;
use UI\Http\Rest\Response\ApiResponse;

final class GetUnreadCountControllerTest extends TestCase {
  #[Test]
  public function itReturnsUnreadCountSuccessfully(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $user = $this->createMock(JwtUser::class);
    $unreadCount = ['count' => 5];

    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn('user-123');

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->callback(function ($envelope) {
        return $envelope instanceof Envelope
          && $envelope->getMessage() instanceof GetUnreadCountQuery;
      }))
      ->willReturn($unreadCount);

    $controller = new GetUnreadCountController();
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

    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn($userId);

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->callback(function (Envelope $envelope) use ($userId) {
        $message = $envelope->getMessage();
        if (!$message instanceof GetUnreadCountQuery) {
          return false;
        }
        $stamp = $envelope->last(HandlerArgumentsStamp::class);
        if (!$stamp instanceof HandlerArgumentsStamp) {
          return false;
        }
        $context = $stamp->getAdditionalArguments();
        return ($context['userId'] ?? null) === $userId;
      }))
      ->willReturn(['count' => 0]);

    $controller = new GetUnreadCountController();
    $controller->setQueryBus($queryBus);

    $controller($user);
  }

  #[Test]
  public function itReturnsZeroUnreadCount(): void {
    $queryBus = $this->createStub(QueryBusInterface::class);
    $user = $this->createStub(JwtUser::class);

    $user->method('getIdentifier')->willReturn('user-123');
    $queryBus->method('ask')->willReturn(['count' => 0]);

    $controller = new GetUnreadCountController();
    $controller->setQueryBus($queryBus);

    $response = $controller($user);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
  }

  #[Test]
  public function itReturnsHighUnreadCount(): void {
    $queryBus = $this->createStub(QueryBusInterface::class);
    $user = $this->createStub(JwtUser::class);

    $user->method('getIdentifier')->willReturn('user-123');
    $queryBus->method('ask')->willReturn(['count' => 999]);

    $controller = new GetUnreadCountController();
    $controller->setQueryBus($queryBus);

    $response = $controller($user);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
  }
}
