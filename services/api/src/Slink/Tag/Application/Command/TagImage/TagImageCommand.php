<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Command\TagImage;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class TagImageCommand implements CommandInterface {
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