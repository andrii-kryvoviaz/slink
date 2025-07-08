<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\UpdateImage;

use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;

final readonly class AdminUpdateImageHandler implements CommandHandlerInterface {
  public function __construct(
    private ConfigurationProviderInterface $configurationProvider,
    private ImageStoreRepositoryInterface $imageRepository,
  ) {
  }
  
  public function __invoke(
    AdminUpdateImageCommand $command,
    string $id
  ): void {
    $image = $this->imageRepository->get(ID::fromString($id));
    
    if (!$image->aggregateRootVersion()) {
      throw new NotFoundException();
    }
    
    $isPublic = $command->getIsPublic();
    if ($this->configurationProvider->get('image.allowOnlyPublicImages')) {
      $isPublic = true;
    }

    $attributes = clone $image->getAttributes()
      ->withDescription($command->getDescription())
      ->withIsPublic($isPublic);
    
    $image->updateAttributes($attributes);
    
    $this->imageRepository->store($image);
  }
}
