<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Notification;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Notification\Application\Command\MarkAllNotificationsRead\MarkAllNotificationsReadCommand;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\HandlerArgumentsStamp;
use UI\Http\Rest\Controller\Notification\MarkAllNotificationsReadController;
use UI\Http\Rest\Response\ApiResponse;

final class MarkAllNotificationsReadControllerTest extends TestCase {
  #[Test]
  public function itMarksAllNotificationsAsReadSuccessfully(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(JwtUser::class);

    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn('user-456');

    $commandBus->expects($this->once())
      ->method('handle')
      ->with($this->callback(function ($envelope) {
        return $envelope instanceof Envelope
          && $envelope->getMessage() instanceof MarkAllNotificationsReadCommand;
      }));

    $controller = new MarkAllNotificationsReadController();

    $reflection = new \ReflectionClass($controller);
    $property = $reflection->getProperty('commandBus');
    $property->setValue($controller, $commandBus);

    $response = $controller($user);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(204, $response->getStatusCode());
  }

  #[Test]
  public function itPassesUserIdToCommand(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(JwtUser::class);
    $userId = 'specific-user-id';

    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn($userId);

    $commandBus->expects($this->once())
      ->method('handle')
      ->with($this->callback(function (Envelope $envelope) use ($userId) {
        $message = $envelope->getMessage();
        if (!$message instanceof MarkAllNotificationsReadCommand) {
          return false;
        }
        $stamp = $envelope->last(HandlerArgumentsStamp::class);
        if (!$stamp instanceof HandlerArgumentsStamp) {
          return false;
        }
        $context = $stamp->getAdditionalArguments();
        return ($context['userId'] ?? null) === $userId;
      }));

    $controller = new MarkAllNotificationsReadController();

    $reflection = new \ReflectionClass($controller);
    $property = $reflection->getProperty('commandBus');
    $property->setValue($controller, $commandBus);

    $controller($user);
  }

  #[Test]
  public function itReturnsEmptyResponse(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $user = $this->createMock(JwtUser::class);

    $user->method('getIdentifier')->willReturn('user-123');
    $commandBus->method('handle');

    $controller = new MarkAllNotificationsReadController();

    $reflection = new \ReflectionClass($controller);
    $property = $reflection->getProperty('commandBus');
    $property->setValue($controller, $commandBus);

    $response = $controller($user);

    $this->assertEquals(204, $response->getStatusCode());
  }
}
