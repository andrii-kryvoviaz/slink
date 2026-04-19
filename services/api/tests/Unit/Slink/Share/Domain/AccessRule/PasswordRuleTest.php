<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Domain\AccessRule;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Share\Domain\Service\ShareUnlockVerifierInterface;
use Slink\Share\Domain\AccessRule\PasswordProtected;
use Slink\Share\Domain\AccessRule\PasswordRule;
use Slink\Share\Domain\Exception\SharePasswordRequiredException;
use Slink\Share\Domain\ValueObject\HashedSharePassword;
use Slink\Shared\Domain\ValueObject\ID;

final class PasswordRuleTest extends TestCase {
  #[Test]
  public function itAllowsWhenSubjectHasNoPassword(): void {
    $rule = new PasswordRule($this->context([]));

    $this->assertTrue($rule->allows($this->subject('share-1', null)));
  }

  #[Test]
  public function itAllowsWhenSubjectHasPasswordAndShareIsVerified(): void {
    $rule = new PasswordRule($this->context(['share-1']));

    $subject = $this->subject('share-1', HashedSharePassword::encode('hunter2'));

    $this->assertTrue($rule->allows($subject));
  }

  #[Test]
  public function itThrowsWhenSubjectHasPasswordAndShareIsNotVerified(): void {
    $rule = new PasswordRule($this->context(['other-share']));

    $subject = $this->subject('share-1', HashedSharePassword::encode('hunter2'));

    $this->expectException(SharePasswordRequiredException::class);

    $rule->allows($subject);
  }

  #[Test]
  public function itThrowsWhenSubjectHasPasswordAndContextIsEmpty(): void {
    $rule = new PasswordRule($this->context([]));

    $subject = $this->subject('share-1', HashedSharePassword::encode('hunter2'));

    $this->expectException(SharePasswordRequiredException::class);

    $rule->allows($subject);
  }

  #[Test]
  public function itSupportsPasswordProtectedSubjects(): void {
    $rule = new PasswordRule($this->context([]));

    $this->assertTrue($rule->supports($this->subject('share-1', null)));
  }

  #[Test]
  public function itDoesNotSupportOtherSubjects(): void {
    $rule = new PasswordRule($this->context([]));

    $this->assertFalse($rule->supports(new \stdClass()));
  }

  private function subject(string $id, ?HashedSharePassword $password): PasswordProtected {
    return new class($id, $password) implements PasswordProtected {
      public function __construct(
        private readonly string $id,
        private readonly ?HashedSharePassword $password,
      ) {}

      public function getId(): string {
        return $this->id;
      }

      public function getPassword(): ?HashedSharePassword {
        return $this->password;
      }
    };
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

      public function isVerified(ID $shareId): bool {
        foreach ($this->verified as $verified) {
          if ($verified->equals($shareId)) {
            return true;
          }
        }

        return false;
      }
    };
  }
}
