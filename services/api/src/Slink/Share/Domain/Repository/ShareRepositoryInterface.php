<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Repository;

use Slink\Share\Infrastructure\ReadModel\View\ShareView;

interface ShareRepositoryInterface {
  public function add(ShareView $share): void;

  public function findById(string $id): ?ShareView;

  public function findByTargetUrl(string $targetUrl): ?ShareView;
}
