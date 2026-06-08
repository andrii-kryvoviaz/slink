<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service\Operation;

use Slink\Image\Domain\ValueObject\Operation\ImageOperation;

interface VipsOperationApplier {
  /**
   * @return class-string<ImageOperation>
   */
  public function operationType(): string;

  public function apply(VipsContext $context, ImageOperation $operation): void;
}
