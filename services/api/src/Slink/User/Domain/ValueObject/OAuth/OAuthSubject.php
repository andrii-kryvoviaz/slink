<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\User\Domain\Enum\OAuthProvider;

final readonly class OAuthSubject extends AbstractValueObject {
  private function __construct(
    private OAuthProvider $provider,
    private SubjectId $sub,
  ) {}

  public function getProvider(): OAuthProvider {
    return $this->provider;
  }

  public function getSub(): SubjectId {
    return $this->sub;
  }

  public static function create(OAuthProvider $provider, SubjectId $sub): self {
    return new self($provider, $sub);
  }

  public static function fromPrimitives(string $providerSlug, string $sub): self {
    return new self(
      OAuthProvider::from($providerSlug),
      SubjectId::fromString($sub),
    );
  }

  public function toKey(): string {
    return sprintf('%s:%s', $this->provider->value, $this->sub->toString());
  }

  public static function fromKey(string $key): self {
    [$provider, $sub] = explode(':', $key, 2);
    return self::fromPrimitives($provider, $sub);
  }
}
