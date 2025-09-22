<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Command\UntagImage;

use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Event\ImageWasUntagged;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class UntagImageHandler implements CommandHandlerInterface {
  public function __construct(
    private TagStoreRepositoryInterface   $tagStore,
    private ImageStoreRepositoryInterface $imageStore,
    private EventDispatcherInterface      $eventDispatcher,
  ) {
  }

  public function __invoke(UntagImageCommand $command, string $userId): void {
    $imageId = ID::fromString($command->getImageId());
    $tagId = ID::fromString($command->getTagId());
    $userIdObject = ID::fromString($userId);

    $tag = $this->tagStore->get($tagId);
    if (!$tag->getUserId()->equals($userIdObject)) {
      throw new \InvalidArgumentException('You can only use your own tags');
    }

    $image = $this->imageStore->get($imageId);
    if ($image->getUserId() && !$image->getUserId()->equals($userIdObject)) {
      throw new \InvalidArgumentException('You can only untag your own images');
    }
    
    $event = new ImageWasUntagged($imageId, $tagId, $userIdObject);
    $this->eventDispatcher->dispatch($event);
  }
}