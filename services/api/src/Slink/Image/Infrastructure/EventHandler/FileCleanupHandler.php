<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\EventHandler;

use Slink\Image\Domain\Event\ImageWasDeleted;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;
use Slink\Shared\Infrastructure\MessageBus\Event\AbstractEventConsumer;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('event_sauce.event_consumer', ['priority' => 10])]
final class FileCleanupHandler extends AbstractEventConsumer {
  public function __construct(
    private readonly ImageRepositoryInterface $imageRepository,
    private readonly StorageInterface $storage,
  ) {
  }

  public function handleImageWasDeleted(ImageWasDeleted $event): void {
    try {
      if ($event->preserveOnDisk) {
        return;
      }

      try {
        $imageView = $this->imageRepository->oneById($event->id->toString());
      } catch (NotFoundException) {
        return;
      }

      $fileName = $imageView->getFileName();
      $attempts = 0;

      while ($attempts < 5) {
        try {
          $this->storage->delete($fileName);
          break;
        } catch (\Throwable) {
          $attempts++;
        }
      }
    } catch (\Throwable) {
    }
  }
}
