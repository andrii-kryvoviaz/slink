<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\UntagImage;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UntagImageCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private string $imageId,

    #[Assert\NotBlank]
    #[Assert\Uuid]
    private string $tagId,
  ) {}

  public function getImageId(): string {
    return $this->imageId;
  }

  public function getTagId(): string {
    return $this->tagId;
  }
}