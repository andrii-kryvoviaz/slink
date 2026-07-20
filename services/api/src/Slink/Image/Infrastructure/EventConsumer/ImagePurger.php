<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\EventConsumer;

use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\MessageBus\Event\AbstractEventConsumer;
use Slink\User\Domain\Event\UserWasPurged;

final class ImagePurger extends AbstractEventConsumer {
  public function __construct(
    private readonly ImageRepositoryInterface $imageRepository,
    private readonly ImageStoreRepositoryInterface $imageStore,
  ) {
  }

  public function handleUserWasPurged(UserWasPurged $event): void {
    foreach ($this->imageRepository->findByUserId($event->id) as $imageView) {
      $image = $this->imageStore->get(ID::fromString($imageView->getUuid()));
      $image->forceDelete(false);

      $this->imageStore->store($image);
    }
  }
}
