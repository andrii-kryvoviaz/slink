<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Service;

use Slink\Share\Domain\Share;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Domain\ValueObject\ShareContext;
use Slink\Share\Domain\ValueObject\ShareResult;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;

interface ShareServiceInterface {
  public function buildContext(ShareableReference $shareable): ShareContext;
  public function resolveUrl(ShareView|Share $share): string;
  public function share(string $shareableId, string $targetUrl): ShareResult;
}
