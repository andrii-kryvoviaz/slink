<?php

declare(strict_types=1);

namespace Slink\Share\Domain\AccessRule;

final readonly class PublishedRule implements ShareAccessRule {
  public function supports(object $subject): bool {
    return $subject instanceof PublicationAware;
  }

  public function allows(PublicationAware $subject): bool {
    return $subject->isPublished();
  }
}
