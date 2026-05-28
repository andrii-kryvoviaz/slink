<?php

declare(strict_types=1);

namespace Slink\Share\Application\Service;

use Slink\Share\Domain\AccessRule\ShareAccessRule;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class ShareAccessGuard {
  /**
   * @param iterable<ShareAccessRule> $rules
   */
  public function __construct(
    #[AutowireIterator('slink.share.access_rule')]
    private iterable $rules,
  ) {}

  public function allows(object $subject): bool {
    foreach ($this->rules as $rule) {
      if (!$rule->supports($subject)) {
        continue;
      }

      if (!$rule->allows($subject)) {
        return false;
      }
    }

    return true;
  }
}
