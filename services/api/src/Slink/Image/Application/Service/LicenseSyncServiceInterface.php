<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service;

use Slink\Image\Domain\Enum\License;
use Slink\Shared\Domain\ValueObject\ID;

interface LicenseSyncServiceInterface {
  public function syncLicenseForUser(ID $userId, ?License $license): void;
}
