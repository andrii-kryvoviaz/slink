<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Analytics;

use Slink\Image\Application\Query\GetImageAnalytics\GetImageAnalyticsQuery;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Domain\Enum\UserRole;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route('/analytics/image', name: 'get_image_analytics', methods: ['GET'])]
#[IsGranted(UserRole::Admin->value)]
final readonly class GetImageAnalyticsController {
  use QueryTrait;
  
  public function __invoke(
    #[MapQueryString] GetImageAnalyticsQuery $query
  ): ApiResponse {
    $analytics = $this->ask($query);
    
    return ApiResponse::fromPayload($analytics);
  }
}
