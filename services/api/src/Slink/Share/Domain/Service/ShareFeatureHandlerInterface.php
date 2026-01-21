<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Service;

use Slink\Share\Domain\ValueObject\ShareContext;

interface ShareFeatureHandlerInterface {
  public function supports(ShareContext $context): bool;
  public function enhance(ShareContext $context): ShareContext;
}
