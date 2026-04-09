<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\GetShareXConfig;

use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\User\Application\Service\ShareXConfigService;
use Slink\User\Domain\Repository\ApiKeyRepositoryInterface;
use Slink\User\Domain\Exception\ApiKeyNotFoundException;

final readonly class GetShareXConfigHandler implements QueryHandlerInterface {
  public function __construct(
    private ShareXConfigService $shareXConfigService,
    private ApiKeyRepositoryInterface $apiKeyRepository
  ) {}

  /**
   * @return array<string, mixed>
   */
  public function __invoke(GetShareXConfigQuery $query, string $userId): array {
    $apiKeyEntity = $this->apiKeyRepository->findByKey($query->getApiKey());

    if (!$apiKeyEntity) {
      throw new ApiKeyNotFoundException('API_KEY_NOT_FOUND');
    }

    if ($apiKeyEntity->getUserId() !== $userId) {
      throw new ApiKeyNotFoundException('API_KEY_NOT_OWNED');
    }

    if ($apiKeyEntity->isExpired()) {
      throw new ApiKeyNotFoundException('API_KEY_EXPIRED');
    }
    
    return $this->shareXConfigService->generateConfig(
      $query->getApiKey(),
      $query->getBaseUrl()
    );
  }
}
