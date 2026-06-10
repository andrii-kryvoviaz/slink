<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Upload;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class UploadPipeline {
  /**
   * @param iterable<UploadStageInterface> $stages
   */
  public function __construct(
    #[AutowireIterator('slink.image.upload_stage')]
    private iterable $stages,
  ) {
  }

  public function run(UploadContext $context): UploadContext {
    foreach ($this->stages as $stage) {
      $context = $stage->process($context);
    }

    return $context;
  }
}
