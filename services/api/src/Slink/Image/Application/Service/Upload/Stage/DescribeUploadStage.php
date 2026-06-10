<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Upload\Stage;

use Slink\Image\Application\Service\Upload\UploadContext;
use Slink\Image\Application\Service\Upload\UploadPhase;
use Slink\Image\Application\Service\Upload\UploadStageInterface;
use Slink\Image\Domain\Exception\UndeterminableImageExtensionException;
use Slink\Image\Domain\Factory\ImageMetadataFactory;
use Slink\Image\Domain\ValueObject\ImageFile;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: UploadPhase::Describe->value)]
final readonly class DescribeUploadStage implements UploadStageInterface {
  public function __construct(
    private ImageMetadataFactory $metadataFactory,
  ) {
  }

  public function process(UploadContext $context): UploadContext {
    $file = $context->file();
    $extension = $file->guessExtension();

    if ($extension === null) {
      throw new UndeterminableImageExtensionException();
    }

    $fileName = sprintf('%s.%s', $context->id(), $extension);
    $mediaFile = ImageFile::fromSymfonyFile($file);
    $metadata = $this->metadataFactory->createFromFile($file);

    return $context->withDescribed($fileName, $mediaFile, $metadata);
  }
}
