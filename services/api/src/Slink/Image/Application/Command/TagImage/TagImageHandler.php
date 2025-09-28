<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\TagImage;

use Slink\Image\Domain\Exception\UnauthorizedImageAccessException;
use Slink\Image\Domain\Exception\UnauthorizedTagAccessException;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;

final readonly class TagImageHandler implements CommandHandlerInterface {
  public function __construct(
    private ImageStoreRepositoryInterface $imageStore,
    private TagStoreRepositoryInterface $tagStore,
  ) {}

  public function __invoke(TagImageCommand $command, string $userId): void {
    $imageId = ID::fromString($command->getImageId());
    $tagId = ID::fromString($command->getTagId());
    $userId = ID::fromString($userId);

    $image = $this->imageStore->get($imageId);
    if (!$image->aggregateRootVersion()) {
      throw new NotFoundException();
    }

    if ($image->getUserId() && !$image->getUserId()->equals($userId)) {
      throw new UnauthorizedImageAccessException($imageId, $userId);
    }

    $tag = $this->tagStore->get($tagId);
    if (!$tag->getUserId()->equals($userId)) {
      throw new UnauthorizedTagAccessException($tagId, $userId);
    }

    $image->tagWith($tagId, $userId);

    $this->imageStore->store($image);
  }
}