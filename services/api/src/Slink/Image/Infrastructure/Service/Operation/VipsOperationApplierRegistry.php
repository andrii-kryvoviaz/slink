<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service\Operation;

use Slink\Image\Domain\ValueObject\Operation\ImageOperation;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class VipsOperationApplierRegistry {
  /** @var array<class-string<ImageOperation>, VipsOperationApplier> */
  private array $_appliers;

  /**
   * @param iterable<VipsOperationApplier> $appliers
   */
  public function __construct(
    #[AutowireIterator('image.vips_operation_applier')]
    iterable $appliers
  ) {
    $map = [];
    foreach ($appliers as $applier) {
      $map[$applier->operationType()] = $applier;
    }

    $this->_appliers = $map;
  }

  public function applierFor(ImageOperation $operation): ?VipsOperationApplier {
    return $this->_appliers[$operation::class] ?? null;
  }
}
