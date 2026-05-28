<?php

declare(strict_types=1);

namespace Slink\Share\Domain\AccessRule;

interface PublicationAware {
  public function isPublished(): bool;
}
