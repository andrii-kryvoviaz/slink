<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\UpdateImage;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateImageCommand implements CommandInterface {
  use EnvelopedMessage;
  
  public function __construct(
    #[Assert\Length(min: 3, max: 255)]
    private ?string $description,
    
    #[Assert\Type('bool')]
    private ?bool   $isPublic,
  ) {
  }
  
  public function getDescription(): ?string {
    return $this->description;
  }
  
  public function getIsPublic(): ?bool {
    return $this->isPublic;
  }
}