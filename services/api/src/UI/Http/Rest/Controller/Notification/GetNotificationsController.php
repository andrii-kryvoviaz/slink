<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Notification;

use Slink\Notification\Application\Query\GetNotifications\GetNotificationsQuery;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
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
    int $page = 1,
    int $limit = 20,
  ): ApiResponse {
    $query = new GetNotificationsQuery($page, $limit);
    $result = $this->ask($query->withContext([
      'userId' => $user->getIdentifier(),
    ]));

    return ApiResponse::collection($result);
  }
}
