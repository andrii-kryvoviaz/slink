<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Double;

use Doctrine\ORM\Event\PostLoadEventArgs;

final class LoadInterleaver {
  /**
   * @param class-string $entityClass
   */
  public function __construct(private readonly string $entityClass, private ?\Closure $write) {
  }

  public function postLoad(PostLoadEventArgs $args): void {
    if ($this->write === null || !$args->getObject() instanceof $this->entityClass) {
      return;
    }

    $write = $this->write;
    $this->write = null;
    $write();
  }

  public function hasRun(): bool {
    return $this->write === null;
  }
}
