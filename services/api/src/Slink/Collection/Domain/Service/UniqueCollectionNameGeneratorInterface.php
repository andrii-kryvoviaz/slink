<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\Service;

use Slink\Collection\Domain\ValueObject\CollectionName;
use Slink\Shared\Domain\ValueObject\ID;

interface UniqueCollectionNameGeneratorInterface {
  public function generate(CollectionName $baseName, ID $userId): CollectionName;
}
