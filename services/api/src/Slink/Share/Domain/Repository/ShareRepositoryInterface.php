<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Repository;

use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;

interface ShareRepositoryInterface {
  public function add(ShareView $share): void;

  public function remove(ShareView $share): void;

  public function findById(string $id): ?ShareView;

  public function findByTargetUrl(string $targetUrl): ?ShareView;

  public function findByShareable(string $shareableId, ShareableType $shareableType): ?ShareView;

  public function removeByShareable(string $shareableId, ShareableType $shareableType): void;
}
