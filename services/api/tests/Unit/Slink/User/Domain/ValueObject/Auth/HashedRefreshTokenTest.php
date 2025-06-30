<?php

namespace Unit\Slink\User\Domain\ValueObject\Auth;

use DateInterval;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use ReflectionException;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\User\Domain\Exception\InvalidRefreshToken;
use Slink\User\Domain\ValueObject\Auth\HashedRefreshToken;
use Tests\Traits\PrivatePropertyTrait;

final class HashedRefreshTokenTest extends TestCase {
  use PrivatePropertyTrait;

  /**
   * @throws DateTimeException
   */
  #[Test]
  public function itCreatesFromHashed(): void {
    $plainRefreshToken = Uuid::uuid4();
    $expiresAt = DateTime::now()->add(new DateInterval('PT1H'));

    $hashedRefreshToken = HashedRefreshToken::createFromHashed($plainRefreshToken, $expiresAt->toString());
    $this->assertInstanceOf(HashedRefreshToken::class, $hashedRefreshToken);
  }

  /**
   * @throws DateTimeException
   */
  #[Test]
  public function itEncodes(): void {
    $hashedRefreshToken = $this->createValidHashedRefreshToken();
    $this->assertInstanceOf(HashedRefreshToken::class, $hashedRefreshToken);
  }

  /**
   * @throws DateTimeException
   */
  #[Test]
  #[DataProvider('provideInvalidRefreshToken')]
  public function itThrowsExceptionForInvalidRefreshToken(string $invalidRefreshToken): void {
    $this->expectException(InvalidRefreshToken::class);

    HashedRefreshToken::encode($invalidRefreshToken);
  }

  /**
   * @return array<int, array<int, string>>
   */
  public static function provideInvalidRefreshToken(): array {
    $validUuid = Uuid::uuid4();
    return [
      ['invalidRefreshToken'],
      [$validUuid . '.' . time()],
      [$validUuid . '.invalidExpiresAt'],
    ];
  }

  /**
   * @throws DateTimeException
   * @throws ReflectionException
   */
  #[Test]
  public function itChecksIfExpired(): void {
    $hashedRefreshToken = $this->createValidHashedRefreshToken();
    $this->assertFalse($hashedRefreshToken->isExpired());

    $expiredRefreshToken = clone $hashedRefreshToken;
    $this->setPrivateProperty($expiredRefreshToken, 'expiresAt', DateTime::fromTimeStamp(time() - 3600));
    $this->assertTrue($expiredRefreshToken->isExpired());
  }

  /**
   * @throws DateTimeException
   */
  private function createValidHashedRefreshToken(): HashedRefreshToken {
    $validRefreshToken = sprintf('%s.%s', Uuid::uuid4(), time() + 3600);
    return HashedRefreshToken::encode($validRefreshToken);
  }
}
