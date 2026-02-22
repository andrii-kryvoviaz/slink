<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth\Oidc;

use Lcobucci\JWT\Encoding\CannotDecodeContent;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use Lcobucci\JWT\Token\UnsupportedHeaderFound;
use Lcobucci\JWT\UnencryptedToken;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Slink\User\Domain\Contracts\OidcClaimsExtractorInterface;
use Slink\User\Domain\Enum\OAuthProvider;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\OAuth\OAuthClaims;
use Slink\User\Domain\ValueObject\OAuth\OAuthSubject;
use Slink\User\Domain\ValueObject\OAuth\SubjectId;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;
use Symfony\Component\DependencyInjection\Attribute\AutowireInline;

final readonly class OidcClaimsExtractor implements OidcClaimsExtractorInterface {
  public function __construct(
    #[AutowireInline(class: \Lcobucci\JWT\Token\Parser::class, arguments: [new AutowireInline(class: JoseEncoder::class)])]
    private Parser $jwtParser,
  ) {}

  /**
   * @param array<string, mixed> $tokenValues
   * @param GenericProvider $oauthClient
   * @param AccessToken $accessToken
   * @param OAuthProviderView $provider
   * @return OAuthClaims
   */
  #[\Override]
  public function extract(array $tokenValues, GenericProvider $oauthClient, AccessToken $accessToken, OAuthProviderView $provider): OAuthClaims {
    $rawClaims = $this->extractRawClaims($tokenValues, $oauthClient, $accessToken);

    $sub = $rawClaims['sub'] ?? null;
    $email = $rawClaims['email'] ?? null;

    if ($sub === null || $email === null) {
      throw new InvalidCredentialsException();
    }

    $subject = OAuthSubject::create(
      OAuthProvider::from($provider->getSlug()),
      SubjectId::fromString((string) $sub),
    );

    $rawName = (string) ($rawClaims['name'] ?? $rawClaims['preferred_username'] ?? $email);

    return new OAuthClaims(
      $subject,
      Email::fromStringOrNull((string) $email),
      DisplayName::fromString($rawName),
      (bool) ($rawClaims['email_verified'] ?? false),
    );
  }

  /**
   * @param array<string, mixed> $tokenValues
   * @param GenericProvider $oauthClient
   * @param AccessToken $accessToken
   * @return array<string, mixed>
   */
  private function extractRawClaims(array $tokenValues, GenericProvider $oauthClient, AccessToken $accessToken): array {
    if (!isset($tokenValues['id_token']) || !is_string($tokenValues['id_token']) || $tokenValues['id_token'] === '') {
      return $oauthClient->getResourceOwner($accessToken)->toArray();
    }

    try {
      $token = $this->jwtParser->parse($tokenValues['id_token']);
    } catch (CannotDecodeContent | InvalidTokenStructure | UnsupportedHeaderFound) {
      return $oauthClient->getResourceOwner($accessToken)->toArray();
    }

    if (!$token instanceof UnencryptedToken) {
      return $oauthClient->getResourceOwner($accessToken)->toArray();
    }

    return $token->claims()->all();
  }
}
