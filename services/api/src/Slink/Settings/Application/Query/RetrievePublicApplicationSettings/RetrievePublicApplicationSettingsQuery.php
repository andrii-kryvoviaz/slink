<?php

declare(strict_types=1);

namespace Slink\Settings\Application\Query\RetrievePublicApplicationSettings;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class RetrievePublicApplicationSettingsQuery implements QueryInterface {
  use EnvelopedMessage;
  
  public function __construct(
    #[Assert\Regex('/^([a-zA-Z0-9]+)(\.[a-zA-Z0-9]+)*$/', message: 'Invalid key format')]
    private ?string $key = null,
  ) {
  }

  public function getKey(): ?string {
    return $this->key;
  }
}
