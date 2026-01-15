<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\UpdateImage;

use Slink\Image\Domain\Enum\License;
use Slink\Image\Domain\Exception\InvalidLicenseException;
use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateImageCommand implements CommandInterface {
  use EnvelopedMessage;
  
  public function __construct(
    #[Assert\Length(max: 255)]
    private ?string $description,
    
    #[Assert\Type('bool')]
    private ?bool $isPublic,
    
    private ?string $license = null,
  ) {
  }
  
  public function getDescription(): ?string {
    return $this->description;
  }
  
  public function getIsPublic(): ?bool {
    return $this->isPublic;
  }

  public function hasLicense(): bool {
    return $this->license !== null;
  }
  
  public function getLicense(): ?License {
    if ($this->license === null) {
      return null;
    }

    $license = License::tryFrom($this->license);

    if ($license === null) {
      throw new InvalidLicenseException($this->license);
    }

    return $license;
  }
}