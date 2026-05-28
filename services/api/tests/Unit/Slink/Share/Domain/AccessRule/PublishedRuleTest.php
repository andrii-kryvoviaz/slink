<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Domain\AccessRule;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Share\Domain\AccessRule\PublicationAware;
use Slink\Share\Domain\AccessRule\PublishedRule;

final class PublishedRuleTest extends TestCase {
  #[Test]
  public function itAllowsWhenSubjectIsPublished(): void {
    $rule = new PublishedRule();
    $subject = $this->subject(true);

    $this->assertTrue($rule->allows($subject));
  }

  #[Test]
  public function itDeniesWhenSubjectIsNotPublished(): void {
    $rule = new PublishedRule();
    $subject = $this->subject(false);

    $this->assertFalse($rule->allows($subject));
  }

  #[Test]
  public function itSupportsPublicationAwareSubjects(): void {
    $rule = new PublishedRule();

    $this->assertTrue($rule->supports($this->subject(true)));
  }

  #[Test]
  public function itDoesNotSupportOtherSubjects(): void {
    $rule = new PublishedRule();

    $this->assertFalse($rule->supports(new \stdClass()));
  }

  private function subject(bool $published): PublicationAware {
    return new class($published) implements PublicationAware {
      public function __construct(private readonly bool $published) {}

      public function isPublished(): bool {
        return $this->published;
      }
    };
  }
}
