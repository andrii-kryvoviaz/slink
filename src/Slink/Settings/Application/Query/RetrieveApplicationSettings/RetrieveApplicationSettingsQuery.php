<?php

declare(strict_types=1);

namespace Slink\Settings\Application\Query\RetrieveApplicationSettings;

use Slink\Shared\Application\Query\QueryInterface;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class RetrieveApplicationSettingsQuery implements QueryInterface {
  public function __construct(
    #[Assert\Regex('/^([a-zA-Z0-9]+)(\.[a-zA-Z0-9]+)*$/', message: 'Invalid key format')]
    private ?string $key = null,
  ) {
  }

  public function getKey(): ?string {
    return $this->key;
  }
}