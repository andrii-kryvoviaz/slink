<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Notification;

use Slink\Notification\Application\Command\MarkNotificationRead\MarkNotificationReadCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/notifications/{notificationId}/read', name: 'mark_notification_read', methods: ['POST'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class MarkNotificationReadController {
  use CommandTrait;

  public function __invoke(
    #[CurrentUser] JWTUser $user,
    string $notificationId,
  ): ApiResponse {
    $command = new MarkNotificationReadCommand();
    $this->handle($command->withContext([
      'notificationId' => $notificationId,
      'userId' => $user->getIdentifier(),
    ]));

    return ApiResponse::empty();
  }
}
