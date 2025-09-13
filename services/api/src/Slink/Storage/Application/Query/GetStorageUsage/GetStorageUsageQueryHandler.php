<?php

declare(strict_types=1);

namespace Slink\Storage\Application\Query\GetStorageUsage;

use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Storage\Domain\Service\StorageUsageServiceInterface;

final readonly class GetStorageUsageQueryHandler implements QueryHandlerInterface {
  public function __construct(
    private StorageUsageServiceInterface $storageUsageService
  ) {
  }
  
  /**
   * @return array<string, mixed>
   */
  public function __invoke(GetStorageUsageQuery $query): array {
    $usage = $this->storageUsageService->getCurrentUsage();
    
    return $usage->toPayload();
  }
}