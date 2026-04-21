<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\Security;

use DateInterval;
use DateTimeImmutable;
use Slink\Share\Domain\Service\ShareUnlockVerifierInterface;
use Slink\Share\Domain\ValueObject\HashedSharePassword;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class ShareUnlockCookieSigner implements ShareUnlockVerifierInterface {
  public function __construct(
    #[Autowire('%kernel.secret%')]
    private string $secret,
    #[Autowire('%kernel.environment%')]
    private string $environment,
    private RequestStack $requestStack,
  ) {}

  public function isVerified(ID $shareId, ?HashedSharePassword $password): bool {
    $request = $this->requestStack->getCurrentRequest();

    if ($request === null) {
      return false;
    }

    return $this->verifyFromCookies($shareId, $request->cookies, $password);
  }

  public function verifyFromCookies(ID $shareId, InputBag $cookies, ?HashedSharePassword $password): bool {
    $value = $cookies->get(self::cookieName($shareId));

    if (!\is_string($value)) {
      return false;
    }

    return $this->verify($shareId, $value, $password);
  }

  public function mint(ID $shareId, ?HashedSharePassword $password): SignedShareUnlock {
    $expiresAt = (new DateTimeImmutable())->add(new DateInterval('PT24H'));
    $token = $this->sign($shareId, $expiresAt, $password);

    return new SignedShareUnlock($token->toString(), $expiresAt);
  }

  public function issueCookie(ID $shareId, ?HashedSharePassword $password): Cookie {
    $signed = $this->mint($shareId, $password);

    return $this->buildCookie($shareId, $signed->value, $signed->expiresAt);
  }

  public function sign(ID $shareId, DateTimeImmutable $expiresAt, ?HashedSharePassword $password): ShareUnlockToken {
    $expiresTimestamp = $expiresAt->getTimestamp();
    $signature = $this->signature($shareId->toString(), $expiresTimestamp, $password);

    return new ShareUnlockToken($expiresTimestamp, $signature);
  }

  public function verify(ID $shareId, string $cookieValue, ?HashedSharePassword $password): bool {
    $token = ShareUnlockToken::fromString($cookieValue);

    if ($token === null || $token->isExpired()) {
      return false;
    }

    $expectedSignature = $this->signature($shareId->toString(), $token->expiresTimestamp, $password);

    return \hash_equals($expectedSignature, $token->signature);
  }

  public function buildCookie(ID $shareId, string $value, DateTimeImmutable $expiresAt): Cookie {
    return Cookie::create(self::cookieName($shareId))
      ->withValue($value)
      ->withExpires($expiresAt)
      ->withPath('/')
      ->withHttpOnly(true)
      ->withSecure($this->environment !== 'dev')
      ->withSameSite(Cookie::SAMESITE_LAX);
  }

  public static function cookieName(ID $shareId): string {
    return "__share_{$shareId->toString()}";
  }

  private function signature(string $shareId, int $expiresTimestamp, ?HashedSharePassword $password): string {
    $passwordVersion = $password?->toString() ?? '';

    return \hash_hmac('sha256', "{$shareId}|{$expiresTimestamp}|{$passwordVersion}", $this->secret);
  }
}
