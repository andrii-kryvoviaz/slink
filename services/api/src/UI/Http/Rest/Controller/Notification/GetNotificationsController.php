<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Notification;

use Slink\Notification\Application\Query\GetNotifications\GetNotificationsQuery;
use Slink\Notification\Domain\Enum\NotificationType;
use Slink\Notification\Domain\Filter\NotificationListFilter;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/notifications', name: 'get_notifications', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class GetNotificationsController {
  use QueryTrait;

  public function __invoke(
    #[CurrentUser] JWTUser $user,
    #[MapQueryParameter(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)] ?NotificationType $type = null,
    #[MapQueryParameter(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)] bool $unread = false,
    #[MapQueryParameter] int $page = 1,
    #[MapQueryParameter] int $limit = 20,
  ): ApiResponse {
    $query = new GetNotificationsQuery(new NotificationListFilter($type, $unread), max(1, $page), min(100, max(1, $limit)));
    $result = $this->ask($query->withContext([
      'userId' => $user->getIdentifier(),
    ]));

    return ApiResponse::collection($result);
  }
}
