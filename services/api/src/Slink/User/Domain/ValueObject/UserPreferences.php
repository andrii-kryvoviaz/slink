<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject;

use Slink\Image\Domain\Enum\License;
use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;
use Slink\User\Domain\Enum\LandingPage;

final readonly class UserPreferences extends AbstractCompoundValueObject {
  private function __construct(
    private array $data = [],
  ) {}

  public static function create(?License $defaultLicense = null, ?LandingPage $defaultLandingPage = null): self {
    return new self([
      'license.default' => $defaultLicense?->value,
      'navigation.landingPage' => $defaultLandingPage?->value,
    ]);
  }

  public static function empty(): self {
    return new self();
  }

  public function getDefaultLicense(): ?License {
    return isset($this->data['license.default'])
      ? License::tryFrom($this->data['license.default'])
      : null;
  }

  public function getDefaultLandingPage(): ?LandingPage {
    return isset($this->data['navigation.landingPage'])
      ? LandingPage::tryFrom($this->data['navigation.landingPage'])
      : null;
  }

  public function withDefaultLicense(?License $license): self {
    return new self([...$this->data, 'license.default' => $license?->value]);
  }

  public function withDefaultLandingPage(?LandingPage $landingPage): self {
    return new self([...$this->data, 'navigation.landingPage' => $landingPage?->value]);
  }

  public function applyChanges(array $payload): self {
    return new self([...$this->data, ...array_filter($payload, fn($v) => $v !== null)]);
  }

  public function toPayload(): array {
    return $this->data;
  }

  public static function fromPayload(array $payload): static {
    return new self($payload);
  }
}
