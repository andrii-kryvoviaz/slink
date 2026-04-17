<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Domain\AccessRule;

use Exception;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Share\Domain\AccessRule\ExpirationAware;
use Slink\Share\Domain\AccessRule\NotExpiredRule;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\Date\DateTime;

final class NotExpiredRuleTest extends TestCase {
  #[Test]
  public function itAllowsWhenSubjectHasNoExpiration(): void {
    $rule = new NotExpiredRule();
    $subject = $this->subject(null);

    $this->assertTrue($rule->allows($subject));
  }

  #[Test]
  public function itAllowsWhenExpirationIsInTheFuture(): void {
    $rule = new NotExpiredRule();
    $future = DateTime::fromString('2099-12-31T23:59:59+00:00');
    $subject = $this->subject($future);

    $this->assertTrue($rule->allows($subject));
  }

  #[Test]
  public function itDeniesWhenExpirationIsInThePast(): void {
    $rule = new NotExpiredRule();
    $past = DateTime::fromString('2000-01-01T00:00:00+00:00');
    $subject = $this->subject($past);

    $this->assertFalse($rule->allows($subject));
  }

  #[Test]
  public function itDeniesAtBoundary(): void {
    $rule = new NotExpiredRule();
    $now = DateTime::now();
    $subject = $this->subject($now);

    $this->assertFalse($rule->allows($subject));
  }

  #[Test]
  public function itDeniesWhenDateTimeExceptionIsThrown(): void {
    $rule = new NotExpiredRule();
    $subject = new class implements ExpirationAware {
      public function getExpiresAt(): ?DateTime {
        throw new DateTimeException(new Exception('failed'));
      }
    };

    $this->assertFalse($rule->allows($subject));
  }

  #[Test]
  public function itSupportsExpirationAwareSubjects(): void {
    $rule = new NotExpiredRule();

    $this->assertTrue($rule->supports($this->subject(null)));
  }

  #[Test]
  public function itDoesNotSupportOtherSubjects(): void {
    $rule = new NotExpiredRule();

    $this->assertFalse($rule->supports(new \stdClass()));
  }

  private function subject(?DateTime $expiresAt): ExpirationAware {
    return new class($expiresAt) implements ExpirationAware {
      public function __construct(private readonly ?DateTime $expiresAt) {}

      public function getExpiresAt(): ?DateTime {
        return $this->expiresAt;
      }
    };
  }
}
