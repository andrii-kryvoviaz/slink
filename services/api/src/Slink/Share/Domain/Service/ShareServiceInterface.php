<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Service;

use Slink\Share\Domain\ValueObject\ShareResult;

interface ShareServiceInterface {
  public function isShorteningEnabled(): bool;

  public function share(string $imageId, string $targetUrl): ShareResult;
}
