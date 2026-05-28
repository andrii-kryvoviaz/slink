<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Application\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Share\Application\Service\ShareAccessGuard;
use Slink\Share\Domain\Service\ShareUnlockVerifierInterface;
use Slink\Share\Domain\AccessRule\ExpirationAware;
use Slink\Share\Domain\AccessRule\NotExpiredRule;
use Slink\Share\Domain\AccessRule\PasswordProtected;
use Slink\Share\Domain\AccessRule\PasswordRule;
use Slink\Share\Domain\AccessRule\PublicationAware;
use Slink\Share\Domain\AccessRule\PublishedRule;
use Slink\Share\Domain\AccessRule\ShareAccessRule;
use Slink\Share\Domain\Exception\ShareExpiredException;
use Slink\Share\Domain\Exception\SharePasswordRequiredException;
use Slink\Share\Domain\ValueObject\HashedSharePassword;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final class ShareAccessGuardTest extends TestCase {
  #[Test]
  public function itAllowsWhenNoRulesDeny(): void {
    $guard = new ShareAccessGuard([
      $this->ruleAllowing(),
      $this->ruleAllowing(),
    ]);

    $this->assertTrue($guard->allows(new \stdClass()));
  }

  #[Test]
  public function itDeniesWhenAnyRuleDenies(): void {
    $guard = new ShareAccessGuard([
      $this->ruleAllowing(),
      $this->ruleDenying(),
      $this->ruleAllowing(),
    ]);

    $this->assertFalse($guard->allows(new \stdClass()));
  }

  #[Test]
  public function itSkipsRulesThatDoNotSupportSubject(): void {
    $guard = new ShareAccessGuard([
      $this->ruleUnsupportedDenying(),
      $this->ruleAllowing(),
    ]);

    $this->assertTrue($guard->allows(new \stdClass()));
  }

  #[Test]
  public function itDoesNotInvokeRulesThatDoNotSupportSubject(): void {
    $rule = new class implements ShareAccessRule {
      public int $invokeCount = 0;

      public function supports(object $subject): bool {
        return false;
      }

      public function allows(object $subject): bool {
        $this->invokeCount++;
        return false;
      }
    };

    $guard = new ShareAccessGuard([$rule]);

    $this->assertTrue($guard->allows(new \stdClass()));
    $this->assertSame(0, $rule->invokeCount);
  }

  #[Test]
  public function itAllowsWhenNoRules(): void {
    $guard = new ShareAccessGuard([]);

    $this->assertTrue($guard->allows(new \stdClass()));
  }

  #[Test]
  public function itAllowsWhenAllRulesAllow(): void {
    $guard = new ShareAccessGuard([
      new PublishedRule(),
      new NotExpiredRule(),
      new PasswordRule($this->context([])),
    ]);

    $subject = $this->subject(true, null, null, 'share-1');

    $this->assertTrue($guard->allows($subject));
  }

  #[Test]
  public function itDeniesWhenPublishedRuleFails(): void {
    $guard = new ShareAccessGuard([
      new PublishedRule(),
      new NotExpiredRule(),
      new PasswordRule($this->context([])),
    ]);

    $subject = $this->subject(false, null, null, 'share-1');

    $this->assertFalse($guard->allows($subject));
  }

  #[Test]
  public function itThrowsShareExpiredWhenNotExpiredRuleFails(): void {
    $guard = new ShareAccessGuard([
      new PublishedRule(),
      new NotExpiredRule(),
      new PasswordRule($this->context([])),
    ]);

    $past = DateTime::fromString('2000-01-01T00:00:00+00:00');
    $subject = $this->subject(true, $past, null, 'share-1');

    $this->expectException(ShareExpiredException::class);

    $guard->allows($subject);
  }

  #[Test]
  public function itThrowsSharePasswordRequiredWhenPasswordRuleFails(): void {
    $guard = new ShareAccessGuard([
      new PublishedRule(),
      new NotExpiredRule(),
      new PasswordRule($this->context([])),
    ]);

    $password = HashedSharePassword::encode('hunter2');
    $subject = $this->subject(true, null, $password, 'share-1');

    $this->expectException(SharePasswordRequiredException::class);

    $guard->allows($subject);
  }

  #[Test]
  public function itAllowsWhenPasswordIsVerified(): void {
    $guard = new ShareAccessGuard([
      new PublishedRule(),
      new NotExpiredRule(),
      new PasswordRule($this->context(['share-1'])),
    ]);

    $password = HashedSharePassword::encode('hunter2');
    $subject = $this->subject(true, null, $password, 'share-1');

    $this->assertTrue($guard->allows($subject));
  }

  /**
   * @param list<string> $verified
   */
  private function context(array $verified): ShareUnlockVerifierInterface {
    $verifiedIds = \array_map(static fn(string $id): ID => ID::fromString($id), $verified);

    return new class($verifiedIds) implements ShareUnlockVerifierInterface {
      /**
       * @param list<ID> $verified
       */
      public function __construct(private readonly array $verified) {}

      public function isVerified(ID $shareId, ?HashedSharePassword $password): bool {
        foreach ($this->verified as $verified) {
          if ($verified->equals($shareId)) {
            return true;
          }
        }

        return false;
      }
    };
  }

  private function subject(
    bool $isPublished,
    ?DateTime $expiresAt,
    ?HashedSharePassword $password,
    string $id,
  ): object {
    return new class($isPublished, $expiresAt, $password, $id) implements PublicationAware, ExpirationAware, PasswordProtected {
      public function __construct(
        private readonly bool $isPublished,
        private readonly ?DateTime $expiresAt,
        private readonly ?HashedSharePassword $password,
        private readonly string $id,
      ) {}

      public function isPublished(): bool {
        return $this->isPublished;
      }

      public function getExpiresAt(): ?DateTime {
        return $this->expiresAt;
      }

      public function getPassword(): ?HashedSharePassword {
        return $this->password;
      }

      public function getId(): string {
        return $this->id;
      }
    };
  }

  private function ruleAllowing(): ShareAccessRule {
    return new class implements ShareAccessRule {
      public function supports(object $subject): bool {
        return true;
      }

      public function allows(object $subject): bool {
        return true;
      }
    };
  }

  private function ruleDenying(): ShareAccessRule {
    return new class implements ShareAccessRule {
      public function supports(object $subject): bool {
        return true;
      }

      public function allows(object $subject): bool {
        return false;
      }
    };
  }

  private function ruleUnsupportedDenying(): ShareAccessRule {
    return new class implements ShareAccessRule {
      public function supports(object $subject): bool {
        return false;
      }

      public function allows(object $subject): bool {
        return false;
      }
    };
  }
}
