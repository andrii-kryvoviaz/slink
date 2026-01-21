<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Command\UpdateCollection;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateCollectionCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private string $id,

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $name,

    #[Assert\Length(max: 500)]
    private string $description = '',
  ) {
  }

  public function getId(): string {
    return $this->id;
  }

  public function getName(): string {
    return $this->name;
  }

  public function getDescription(): string {
    return $this->description;
  }
}
