<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Notification;

use Slink\Notification\Application\Command\MarkAllNotificationsRead\MarkAllNotificationsReadCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/notifications/mark-all-read', name: 'mark_all_notifications_read', methods: ['POST'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class MarkAllNotificationsReadController {
  use CommandTrait;

  public function __invoke(
    #[CurrentUser] JWTUser $user,
  ): ApiResponse {
    $command = new MarkAllNotificationsReadCommand();
    $this->handle($command->withContext([
      'userId' => $user->getIdentifier(),
    ]));

    return ApiResponse::empty();
  }
}
