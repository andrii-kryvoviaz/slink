<?php

declare(strict_types=1);

namespace Slink\Settings\Application\Query\RetrieveSettings;

use Slink\Settings\Domain\Enum\ConfigurationProvider;
use Slink\Shared\Application\Query\QueryInterface;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class RetrieveSettingsQuery implements QueryInterface {
  public function __construct(
    #[Assert\Choice(callback: [ConfigurationProvider::class, 'values'])]
    private string $provider = ConfigurationProvider::Default->value
  ) {
  }
  
  public function getProvider(): string {
    return $this->provider;
  }
}