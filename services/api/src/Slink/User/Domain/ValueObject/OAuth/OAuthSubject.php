<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;

final readonly class OAuthSubject extends AbstractValueObject {
  private function __construct(
    private ProviderSlug $provider,
    private SubjectId $sub,
  ) {}

  public function getProvider(): ProviderSlug {
    return $this->provider;
  }

  public function getSub(): SubjectId {
    return $this->sub;
  }

  public static function create(ProviderSlug $provider, SubjectId $sub): self {
    return new self($provider, $sub);
  }

  public static function fromPrimitives(string $providerSlug, string $sub): self {
    return new self(
      ProviderSlug::fromString($providerSlug),
      SubjectId::fromString($sub),
    );
  }

  public function toKey(): string {
    return sprintf('%s:%s', $this->provider->toString(), $this->sub->toString());
  }

  public static function fromKey(string $key): self {
    [$provider, $sub] = explode(':', $key, 2);
    return new self(ProviderSlug::fromString($provider), SubjectId::fromString($sub));
  }
}
