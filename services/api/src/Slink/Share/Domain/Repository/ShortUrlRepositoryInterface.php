<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Repository;

use Slink\Share\Infrastructure\ReadModel\View\ShortUrlView;

interface ShortUrlRepositoryInterface {
  public function add(ShortUrlView $shortUrl): void;

  public function findByShortCode(string $shortCode): ?ShortUrlView;

  public function existsByShortCode(string $shortCode): bool;
}
