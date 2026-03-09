<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\BatchReassignImages\Operation;

use Slink\Image\Application\Command\BatchReassignImages\BatchReassignImagesCommand;
use Slink\Image\Domain\Image;
use Slink\Shared\Domain\ValueObject\ID;

interface BatchReassignOperationInterface {
  public function supports(BatchReassignImagesCommand $command, string $imageId): bool;
  public function apply(Image $image, BatchReassignImagesCommand $command, string $imageId, ID $userId): void;
}
