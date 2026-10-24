<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Message;

use Slink\Image\Domain\Enum\ImageFeedEventType;

final readonly class ImageFeedChanged {
  public function __construct(
    public string $imageId,
    public ImageFeedEventType $eventType,
  ) {
  }
}
