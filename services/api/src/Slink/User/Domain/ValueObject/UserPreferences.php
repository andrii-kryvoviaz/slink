<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject;

use Slink\Image\Domain\Enum\License;
use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;

final readonly class UserPreferences extends AbstractCompoundValueObject {
  private function __construct(
    private ?string $defaultLicense = null,
  ) {}

  public static function create(?License $defaultLicense = null): self {
    return new self($defaultLicense?->value);
  }

  public static function empty(): self {
    return new self();
  }

  public function getDefaultLicense(): ?License {
    return $this->defaultLicense ? License::tryFrom($this->defaultLicense) : null;
  }

  public function with(string $key, mixed $value): self {
    return match($key) {
      'defaultLicense' => new self(
        $value instanceof License ? $value->value : $value,
      ),
      default => $this,
    };
  }

  public function toPayload(): array {
    return [
      'defaultLicense' => $this->defaultLicense,
    ];
  }

  public static function fromPayload(array $payload): static {
    return new self(
      $payload['defaultLicense'] ?? null,
    );
  }
}
