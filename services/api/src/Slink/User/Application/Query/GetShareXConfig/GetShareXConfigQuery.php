<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\GetShareXConfig;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class GetShareXConfigQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\Length(min: 1, max: 255)]
    private ?string $baseUrl = null,
    
    #[Assert\NotBlank(message: 'API key is required. Create one first from your profile page.')]
    #[Assert\Length(min: 1, max: 255)]
    private string $apiKey = ''
  ) {}

  public function getBaseUrl(): string {
    return $this->baseUrl ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
  }

  public function getApiKey(): string {
    return $this->apiKey;
  }
}
