<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Notification;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Notification\Application\Command\MarkNotificationRead\MarkNotificationReadCommand;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\HandlerArgumentsStamp;
use UI\Http\Rest\Controller\Notification\MarkNotificationReadController;
use UI\Http\Rest\Response\ApiResponse;

final class MarkNotificationReadControllerTest extends TestCase {
  #[Test]
  public function itMarksNotificationAsReadSuccessfully(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(JwtUser::class);
    $notificationId = 'notification-123';

    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn('user-456');

    $commandBus->expects($this->once())
      ->method('handle')
      ->with($this->callback(function ($envelope) {
        return $envelope instanceof Envelope
          && $envelope->getMessage() instanceof MarkNotificationReadCommand;
      }));

    $controller = new MarkNotificationReadController();

    $reflection = new \ReflectionClass($controller);
    $property = $reflection->getProperty('commandBus');
    $property->setValue($controller, $commandBus);

    $response = $controller($user, $notificationId);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(204, $response->getStatusCode());
  }

  #[Test]
  public function itPassesNotificationIdToCommand(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(JwtUser::class);
    $notificationId = 'specific-notification-id';

    $user->method('getIdentifier')->willReturn('user-123');

    $commandBus->expects($this->once())
      ->method('handle')
      ->with($this->callback(function (Envelope $envelope) use ($notificationId) {
        $message = $envelope->getMessage();
        if (!$message instanceof MarkNotificationReadCommand) {
          return false;
        }
        $stamp = $envelope->last(HandlerArgumentsStamp::class);
        if (!$stamp instanceof HandlerArgumentsStamp) {
          return false;
        }
        $context = $stamp->getAdditionalArguments();
        return ($context['notificationId'] ?? null) === $notificationId;
      }));

    $controller = new MarkNotificationReadController();

    $reflection = new \ReflectionClass($controller);
    $property = $reflection->getProperty('commandBus');
    $property->setValue($controller, $commandBus);

    $controller($user, $notificationId);
  }

  #[Test]
  public function itPassesUserIdToCommand(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(JwtUser::class);
    $userId = 'owner-user-id';
    $notificationId = 'notification-123';

    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn($userId);

    $commandBus->expects($this->once())
      ->method('handle')
      ->with($this->callback(function (Envelope $envelope) use ($userId) {
        $message = $envelope->getMessage();
        if (!$message instanceof MarkNotificationReadCommand) {
          return false;
        }
        $stamp = $envelope->last(HandlerArgumentsStamp::class);
        if (!$stamp instanceof HandlerArgumentsStamp) {
          return false;
        }
        $context = $stamp->getAdditionalArguments();
        return ($context['userId'] ?? null) === $userId;
      }));

    $controller = new MarkNotificationReadController();

    $reflection = new \ReflectionClass($controller);
    $property = $reflection->getProperty('commandBus');
    $property->setValue($controller, $commandBus);

    $controller($user, $notificationId);
  }

  #[Test]
  public function itReturnsEmptyResponse(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(JwtUser::class);

    $user->method('getIdentifier')->willReturn('user-123');
    $commandBus->method('handle');

    $controller = new MarkNotificationReadController();

    $reflection = new \ReflectionClass($controller);
    $property = $reflection->getProperty('commandBus');
    $property->setValue($controller, $commandBus);

    $response = $controller($user, 'notification-456');

    $this->assertEquals(204, $response->getStatusCode());
  }
}
