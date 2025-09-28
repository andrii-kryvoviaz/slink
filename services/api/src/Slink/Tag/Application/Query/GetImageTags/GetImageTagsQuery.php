<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Query\GetImageTags;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class GetImageTagsQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private string $imageId,
  ) {
  }

  public function getImageId(): string {
    return $this->imageId;
  }
}