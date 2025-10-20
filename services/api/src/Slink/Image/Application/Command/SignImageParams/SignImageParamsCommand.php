<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\SignImageParams;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class SignImageParamsCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    private string $imageId,
    private ?int $width = null,
    private ?int $height = null,
    private bool $crop = false
  ) {
  }

  public function getImageId(): string {
    return $this->imageId;
  }

  public function getWidth(): ?int {
    return $this->width;
  }

  public function getHeight(): ?int {
    return $this->height;
  }

  public function isCropped(): bool {
    return $this->crop;
  }
}
