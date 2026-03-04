<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\ValueObject\OAuth;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\Exception\InvalidValueObjectException;
use Slink\User\Domain\ValueObject\OAuth\AuthorizationCode;

final class AuthorizationCodeTest extends TestCase {

  #[Test]
  public function itCreatesFromValidString(): void {
    $code = AuthorizationCode::fromString('auth-code-xyz');

    $this->assertInstanceOf(AuthorizationCode::class, $code);
    $this->assertSame('auth-code-xyz', $code->toString());
  }

  #[Test]
  public function itThrowsOnEmptyString(): void {
    $this->expectException(InvalidValueObjectException::class);
    $this->expectExceptionMessage('Invalid AuthorizationCode: cannot be empty');

    AuthorizationCode::fromString('');
  }
}
