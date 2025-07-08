<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\GetApiKeys;

use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\User\Domain\Repository\ApiKeyRepositoryInterface;

final readonly class GetApiKeysHandler implements QueryHandlerInterface {
  public function __construct(
    private ApiKeyRepositoryInterface $apiKeyRepository
  ) {}

  /**
   * @return array<int, array<string, mixed>>
   */
  public function __invoke(GetApiKeysQuery $query): array {
    $apiKeys = $this->apiKeyRepository->findByUserId($query->getUserId());
    return array_map(fn($apiKey) => $apiKey->toPayload(), $apiKeys);
  }
}
