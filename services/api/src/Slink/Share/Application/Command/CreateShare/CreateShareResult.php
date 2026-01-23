<?php

declare(strict_types=1);

namespace Slink\Share\Application\Command\CreateShare;

use Slink\Share\Domain\Share;

final readonly class CreateShareResult {
  private function __construct(
    private Share $share,
    private bool $created,
  ) {}

  public static function created(Share $share): self {
    return new self($share, true);
  }

  public static function existing(Share $share): self {
    return new self($share, false);
  }

  public function getShare(): Share {
    return $this->share;
  }

  public function wasCreated(): bool {
    return $this->created;
  }
}
