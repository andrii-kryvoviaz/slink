<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\RevokeApiKey;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class RevokeApiKeyCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\NotBlank]
    private string $keyId
  ) {}

  public function getKeyId(): string {
    return $this->keyId;
  }
}
