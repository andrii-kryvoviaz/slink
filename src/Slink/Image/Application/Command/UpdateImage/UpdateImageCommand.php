<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\UpdateImage;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Validator\Constraints as Assert;

final class UpdateImageCommand implements CommandInterface {
  private string $id;
  
  public function __construct(
    #[Assert\Length(min: 3, max: 255)]
    private readonly ?string $description,
    
    #[Assert\Type('bool')]
    private readonly ?bool $isPublic,
  ) {
  }
  
  public function getId(): ID {
    return ID::fromString($this->id);
  }
  
  public function withId(string $id): self {
    $this->id = $id;
    
    return $this;
  }
  
  public function getDescription(): ?string {
    return $this->description;
  }
  
  public function getIsPublic(): ?bool {
    return $this->isPublic;
  }
}