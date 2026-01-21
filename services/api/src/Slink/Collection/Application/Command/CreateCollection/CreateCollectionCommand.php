<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Command\CreateCollection;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateCollectionCommand implements CommandInterface {
  use EnvelopedMessage;

  private ID $id;

  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $name,

    #[Assert\Length(max: 500)]
    private string $description = '',
  ) {
    $this->id = ID::generate();
  }

  public function getId(): ID {
    return $this->id;
  }

  public function getName(): string {
    return $this->name;
  }

  public function getDescription(): string {
    return $this->description;
  }
}
