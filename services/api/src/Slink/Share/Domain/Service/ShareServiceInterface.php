<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Service;

use Slink\Share\Domain\Share;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Domain\ValueObject\ShareContext;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;

interface ShareServiceInterface {
  public function buildContext(ShareableReference $shareable): ShareContext;
  public function resolveUrl(ShareView|Share $share, bool $isAbsolute = true): string;
}
