<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Analytics;

use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Application\Query\User\GetUserAnalytics\GetUserAnalyticsQuery;
use Slink\User\Domain\Enum\UserRole;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/analytics/user', name: 'get_user_analytics', methods: ['GET'])]
#[IsGranted(UserRole::Admin->value)]
final class GetUserAnalyticsController {
  use QueryTrait;
  
  public function __invoke(
    #[MapQueryString] GetUserAnalyticsQuery $query
  ): ApiResponse {
    $analytics = $this->ask($query);
    
    return ApiResponse::fromPayload($analytics);
  }
}
