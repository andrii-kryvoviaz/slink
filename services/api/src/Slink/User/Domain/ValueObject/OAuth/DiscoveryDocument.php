<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;

final readonly class DiscoveryDocument extends AbstractCompoundValueObject {
  private function __construct(
    private string $authorizationEndpoint,
    private string $tokenEndpoint,
    private string $userinfoEndpoint,
    private string $jwksUri,
    private string $issuer,
  ) {}

  /**
   * @param array{authorizationEndpoint: string, tokenEndpoint: string, userinfoEndpoint: string, jwksUri: string, issuer: string} $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      $payload['authorizationEndpoint'],
      $payload['tokenEndpoint'],
      $payload['userinfoEndpoint'],
      $payload['jwksUri'],
      $payload['issuer'],
    );
  }

  /**
   * @return array{authorizationEndpoint: string, tokenEndpoint: string, userinfoEndpoint: string, jwksUri: string, issuer: string}
   */
  public function toPayload(): array {
    return [
      'authorizationEndpoint' => $this->authorizationEndpoint,
      'tokenEndpoint' => $this->tokenEndpoint,
      'userinfoEndpoint' => $this->userinfoEndpoint,
      'jwksUri' => $this->jwksUri,
      'issuer' => $this->issuer,
    ];
  }

  public function getAuthorizationEndpoint(): string {
    return $this->authorizationEndpoint;
  }

  public function getTokenEndpoint(): string {
    return $this->tokenEndpoint;
  }

  public function getUserinfoEndpoint(): string {
    return $this->userinfoEndpoint;
  }

  public function getJwksUri(): JwksUri {
    return JwksUri::fromString($this->jwksUri);
  }

  public function getIssuer(): Issuer {
    $issuer = Issuer::fromString($this->issuer);
    assert($issuer !== null);

    return $issuer;
  }
}
