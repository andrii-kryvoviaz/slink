<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Service;

use Slink\Share\Domain\Enum\ShareableType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface ShareableMetaProviderInterface {
  public function supports(): ShareableType;

  /**
   * @param list<string> $ids
   * @return array<string, array{id: string, name: string, previewUrl: ?string}>
   */
  public function resolve(array $ids): array;
}
