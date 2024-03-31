<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\DeleteImage;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class DeleteImageCommand implements CommandInterface {
  use EnvelopedMessage;
  
  public function __construct(
    #[Assert\Type('boolean')]
    private bool $preserveOnDisk = false,
  ) {}
  
  public function preserveOnDisk(): bool {
    return $this->preserveOnDisk;
  }
}