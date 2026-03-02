<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\ValueObject\OAuth;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\Exception\InvalidOAuthValueException;
use Slink\User\Domain\ValueObject\OAuth\OAuthState;

final class OAuthStateTest extends TestCase {

  #[Test]
  public function itCreatesFromValidString(): void {
    $state = OAuthState::fromString('random-state-token');

    $this->assertInstanceOf(OAuthState::class, $state);
    $this->assertSame('random-state-token', $state->toString());
  }

  #[Test]
  public function itThrowsOnEmptyString(): void {
    $this->expectException(InvalidOAuthValueException::class);
    $this->expectExceptionMessage('Invalid OAuthState: cannot be empty');

    OAuthState::fromString('');
  }
}
