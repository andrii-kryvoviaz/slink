<?php

declare(strict_types=1);

namespace Slink\Share\Domain\AccessRule;

/**
 * @method bool allows(object $subject)
 */
interface ShareAccessRule {
  public function supports(object $subject): bool;
}
