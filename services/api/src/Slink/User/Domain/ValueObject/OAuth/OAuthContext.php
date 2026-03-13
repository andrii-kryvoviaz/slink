<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;

final readonly class OAuthContext extends AbstractValueObject {
  private function __construct(
    private ProviderSlug $provider,
    private RedirectUri $redirectUri,
    private ?PkceVerifier $pkceVerifier,
  ) {}

  public static function create(
    ProviderSlug $provider,
    RedirectUri $redirectUri,
    ?PkceVerifier $pkceVerifier,
  ): self {
    return new self($provider, $redirectUri, $pkceVerifier);
  }

  /**
   * @param array{provider: string, redirectUri: string, pkceVerifier: ?string} $payload
   */
  public static function fromPayload(array $payload): self {
    return new self(
      ProviderSlug::fromString($payload['provider']),
      RedirectUri::fromString($payload['redirectUri']),
      PkceVerifier::fromString($payload['pkceVerifier']),
    );
  }

  /**
   * @return array{provider: string, redirectUri: string, pkceVerifier: ?string}
   */
  public function toPayload(): array {
    return [
      'provider' => $this->provider->toString(),
      'redirectUri' => $this->redirectUri->toString(),
      'pkceVerifier' => $this->pkceVerifier?->toString(),
    ];
  }

  public function getProvider(): ProviderSlug {
    return $this->provider;
  }

  public function getRedirectUri(): RedirectUri {
    return $this->redirectUri;
  }

  public function getPkceVerifier(): ?PkceVerifier {
    return $this->pkceVerifier;
  }
}
