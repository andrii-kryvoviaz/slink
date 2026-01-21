<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Repository;

use Slink\Share\Domain\Share;
use Slink\Shared\Domain\ValueObject\ID;

interface ShareStoreRepositoryInterface {
  public function get(ID $id): Share;

  public function findByTargetUrl(string $targetUrl): ?Share;

  public function store(Share $share): void;
}
