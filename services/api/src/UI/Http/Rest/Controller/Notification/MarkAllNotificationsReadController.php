<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Notification;

use Slink\Notification\Domain\Repository\NotificationRepositoryInterface;
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
  public function __construct(
    private NotificationRepositoryInterface $notificationRepository,
  ) {
  }

  public function __invoke(
    #[CurrentUser] JWTUser $user,
  ): ApiResponse {
    $this->notificationRepository->markAllAsReadByUserId($user->getIdentifier());
    return ApiResponse::empty();
  }
}
