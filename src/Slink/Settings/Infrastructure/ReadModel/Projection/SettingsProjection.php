<?php

declare(strict_types=1);

namespace Slink\Settings\Infrastructure\ReadModel\Projection;

use Slink\Settings\Domain\Event\SettingsChanged;
use Slink\Settings\Domain\Repository\SettingsRepositoryInterface;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;

final class SettingsProjection extends AbstractProjection {
  
  public function __construct(
    private readonly SettingsRepositoryInterface $repository
  ) {
  }
  
  public function handleSettingsChanged(SettingsChanged $event): void {
    $this->repository->setBulk($event->settings->toNormalizedPayload(), $event->category);
  }
}