<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\ReadModel;

use Doctrine\ORM\Mapping as ORM;

trait VersionedView {
  #[ORM\Version]
  #[ORM\Column(type: 'integer', options: ['default' => 1])]
  private int $version = 1;

  public function getVersion(): int {
    return $this->version;
  }
}
