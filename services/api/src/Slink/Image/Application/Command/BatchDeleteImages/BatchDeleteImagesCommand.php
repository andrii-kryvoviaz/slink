<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\BatchDeleteImages;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class BatchDeleteImagesCommand implements CommandInterface {
  use EnvelopedMessage;

  /**
   * @param array<string> $imageIds
   */
  public function __construct(
    #[Assert\NotBlank]
    #[Assert\All([new Assert\Uuid()])]
    private array $imageIds,
    #[Assert\Type('boolean')]
    private bool $preserveOnDisk = false,
  ) {}

  /**
   * @return array<string>
   */
  public function imageIds(): array {
    return $this->imageIds;
  }

  public function preserveOnDisk(): bool {
    return $this->preserveOnDisk;
  }
}
