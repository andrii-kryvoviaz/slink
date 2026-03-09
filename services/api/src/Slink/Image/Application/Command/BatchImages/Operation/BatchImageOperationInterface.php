<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\BatchImages\Operation;

use Slink\Image\Application\Command\BatchImages\BatchImagesCommand;
use Slink\Image\Domain\Image;
use Slink\Shared\Domain\ValueObject\ID;

interface BatchImageOperationInterface {
  public function supports(BatchImagesCommand $command): bool;

  public function apply(Image $image, BatchImagesCommand $command, ID $userId): void;
}
