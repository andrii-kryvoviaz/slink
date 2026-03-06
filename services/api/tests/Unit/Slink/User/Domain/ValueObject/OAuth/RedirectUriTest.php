<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\ValueObject\OAuth;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\Exception\InvalidValueObjectException;
use Slink\User\Domain\ValueObject\OAuth\RedirectUri;

final class RedirectUriTest extends TestCase {

  #[Test]
  public function itCreatesFromValidUrl(): void {
    $uri = RedirectUri::fromString('https://example.com/callback');

    $this->assertInstanceOf(RedirectUri::class, $uri);
    $this->assertSame('https://example.com/callback', $uri->toString());
  }

  #[Test]
  public function itThrowsOnEmptyString(): void {
    $this->expectException(InvalidValueObjectException::class);
    $this->expectExceptionMessage('Invalid RedirectUri: invalid URI format');

    RedirectUri::fromString('');
  }

  #[Test]
  public function itThrowsOnInvalidUri(): void {
    $this->expectException(InvalidValueObjectException::class);
    $this->expectExceptionMessage('Invalid RedirectUri: invalid URI format');

    RedirectUri::fromString('not-a-valid-url');
  }
}
