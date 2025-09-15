<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\CalculateImageHash;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class CalculateImageHashCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    private ID $imageId
  ) {
  }

  public function getImageId(): ID {
    return $this->imageId;
  }
}