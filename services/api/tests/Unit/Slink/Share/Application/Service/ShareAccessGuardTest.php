<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Application\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Share\Application\Service\ShareAccessGuard;
use Slink\Share\Domain\AccessRule\ShareAccessRule;

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
