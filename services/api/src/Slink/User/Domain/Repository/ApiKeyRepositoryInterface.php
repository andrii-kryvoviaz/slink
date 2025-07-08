<?php

declare(strict_types=1);

namespace Slink\User\Domain\Repository;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Infrastructure\ReadModel\View\ApiKeyView;

interface ApiKeyRepositoryInterface {
  public function save(ApiKeyView $apiKey): void;
  
  public function findByKey(string $key): ?ApiKeyView;
  
  /**
   * @return array<int, ApiKeyView>
   */
  public function findByUserId(ID $userId): array;
  
  public function findById(string $keyId): ?ApiKeyView;
  
  public function delete(ApiKeyView $apiKey): void;
}
