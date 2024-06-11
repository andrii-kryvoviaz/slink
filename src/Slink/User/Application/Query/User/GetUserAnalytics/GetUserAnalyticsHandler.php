<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\User\GetUserAnalytics;

use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\User\Domain\Repository\UserAnalyticsRepositoryInterface;

final readonly class GetUserAnalyticsHandler implements QueryHandlerInterface {
  
  public function __construct(private UserAnalyticsRepositoryInterface $repository) {
  }
  
  /**
   * @param GetUserAnalyticsQuery $query
   * @return array<string, mixed>
   */
  public function __invoke(GetUserAnalyticsQuery $query): array {
    $data = $this->repository->getAnalytics();
    
    return [
      'data' => $data
    ];
  }
}