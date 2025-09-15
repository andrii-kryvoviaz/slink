<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\CalculateImageHash;

use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Image\Domain\Service\ImageHashCalculatorInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;

final readonly class CalculateImageHashHandler implements CommandHandlerInterface {
  public function __construct(
    private ImageStoreRepositoryInterface $imageStoreRepository,
    private ImageRepositoryInterface      $imageRepository,
    private ImageHashCalculatorInterface  $hashCalculator,
    private StorageInterface              $storage
  ) {
  }

  public function __invoke(CalculateImageHashCommand $command): void {
    $imageView = $this->imageRepository->oneById($command->getImageId()->toString());

    $fileName = $imageView->getFileName();
    $imageOptions = ImageOptions::fromPayload([
      'fileName' => $fileName,
      'mimeType' => $imageView->getMimeType()
    ]);

    $imageContent = $this->storage->getImage($imageOptions);

    if ($imageContent === null) {
      throw new \RuntimeException(sprintf('Could not read image content for %s', $fileName));
    }

    $sha1Hash = $this->hashCalculator->calculateFromContent($imageContent);

    $image = $this->imageStoreRepository->get($command->getImageId());
    $currentMetadata = $image->getMetadata();

    $updatedMetadata = $currentMetadata->withHash($sha1Hash);
    $image->updateMetadata($updatedMetadata);

    $this->imageStoreRepository->store($image);
  }
}