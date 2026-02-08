<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\Projection;

use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;
use Slink\User\Domain\Event\ApiKeyWasCreated;
use Slink\User\Domain\Event\ApiKeyWasRevoked;
use Slink\User\Domain\Repository\ApiKeyRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\ApiKeyView;

final  class ApiKeyProjection extends AbstractProjection {
  public function __construct(
    private readonly ApiKeyRepositoryInterface $repository
  ) {}

  public function handleApiKeyWasCreated(ApiKeyWasCreated $event): void {
    $apiKey = ApiKeyView::create(
      $event->keyId,
      $event->userId->toString(),
      $event->keyHash,
      $event->name,
      $event->createdAt,
      $event->expiresAt
    );

    $this->repository->save($apiKey);
  }

  public function handleApiKeyWasRevoked(ApiKeyWasRevoked $event): void {
    $apiKey = $this->repository->findById($event->keyId);
    
    if ($apiKey !== null) {
      $this->repository->delete($apiKey);
    }
  }
}
