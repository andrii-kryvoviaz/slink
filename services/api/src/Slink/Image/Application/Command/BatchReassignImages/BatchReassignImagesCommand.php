<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\BatchReassignImages;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class BatchReassignImagesCommand implements CommandInterface {
  use EnvelopedMessage;

  /**
   * @param array<string, array{tagIds?: string[], collectionIds?: string[]}> $assignments
   */
  public function __construct(
    #[Assert\NotBlank]
    private array $assignments,
  ) {}

  /**
   * @return array<string>
   */
  public function getImageIds(): array {
    return array_keys($this->assignments);
  }

  /**
   * @return array<string>|null
   */
  public function getTagIdsForImage(string $imageId): ?array {
    return $this->assignments[$imageId]['tagIds'] ?? null;
  }

  /**
   * @return array<string>|null
   */
  public function getCollectionIdsForImage(string $imageId): ?array {
    return $this->assignments[$imageId]['collectionIds'] ?? null;
  }
}
