<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Notification;

use Slink\Notification\Application\Query\GetUnreadCount\GetUnreadCountQuery;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/notifications/unread-count', name: 'get_unread_count', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class GetUnreadCountController {
  use QueryTrait;

  public function __invoke(
    #[CurrentUser] JWTUser $user,
  ): ApiResponse {
    $query = new GetUnreadCountQuery();
    $result = $this->ask($query->withContext([
      'userId' => $user->getIdentifier(),
    ]));

    return ApiResponse::fromPayload($result);
  }
}
