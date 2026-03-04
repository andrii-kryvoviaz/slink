<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\ValueObject\OAuth;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\Exception\InvalidValueObjectException;
use Slink\User\Domain\ValueObject\OAuth\PkceVerifier;

final class PkceVerifierTest extends TestCase {

  #[Test]
  public function itCreatesFromValidString(): void {
    $verifier = PkceVerifier::fromString('pkce-verifier-value');

    $this->assertInstanceOf(PkceVerifier::class, $verifier);
    $this->assertSame('pkce-verifier-value', $verifier->toString());
  }

  #[Test]
  public function itThrowsOnEmptyString(): void {
    $this->expectException(InvalidValueObjectException::class);
    $this->expectExceptionMessage('Invalid PkceVerifier: cannot be empty');

    PkceVerifier::fromString('');
  }

  #[Test]
  public function itReturnsNullFromNullableStringWithNull(): void {
    $result = PkceVerifier::fromString(null);

    $this->assertNull($result);
  }

  #[Test]
  public function itReturnsInstanceFromNullableStringWithValue(): void {
    $result = PkceVerifier::fromString('some-verifier');

    $this->assertInstanceOf(PkceVerifier::class, $result);
    $this->assertSame('some-verifier', $result->toString());
  }
}
