<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\BatchImages;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class BatchImagesCommand implements CommandInterface {
  use EnvelopedMessage;

  /**
   * @param array<string> $imageIds
   * @param array<string>|null $tagIds
   */
  public function __construct(
    #[Assert\NotBlank]
    #[Assert\All([new Assert\Uuid()])]
    private array $imageIds,
    private ?bool $isPublic = null,
    #[Assert\All([new Assert\Uuid()])]
    private ?array $tagIds = null,
    #[Assert\Uuid]
    private ?string $collectionId = null,
  ) {}

  /**
   * @return array<string>
   */
  public function imageIds(): array {
    return $this->imageIds;
  }

  public function isPublic(): ?bool {
    return $this->isPublic;
  }

  /**
   * @return array<string>|null
   */
  public function tagIds(): ?array {
    return $this->tagIds;
  }

  public function collectionId(): ?string {
    return $this->collectionId;
  }
}
