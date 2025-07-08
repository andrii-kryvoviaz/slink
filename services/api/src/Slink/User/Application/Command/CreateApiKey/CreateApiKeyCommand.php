<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\CreateApiKey;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Slink\User\Infrastructure\Validator\FutureDate;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateApiKeyCommand implements CommandInterface {
  use EnvelopedMessage;

  private readonly ID $id;
  private string $userId = '';

  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 100)]
    private readonly string $name,
    
    #[FutureDate]
    private readonly ?string $expiresAt = null
  ) {
    $this->id = ID::generate();
  }

  public function withUserId(string $userId): self {
    $new = clone $this;
    $new->userId = $userId;
    return $new;
  }

  public function getId(): ID {
    return $this->id;
  }

  public function getUserId(): string {
    return $this->userId;
  }

  public function getName(): string {
    return $this->name;
  }

  public function getExpiresAt(): ?DateTime {
    if ($this->expiresAt === null) {
      return null;
    }
    
    return DateTime::fromString($this->expiresAt);
  }
}
