<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\Service;

use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Collection\Domain\Service\UniqueCollectionNameGeneratorInterface;
use Slink\Collection\Domain\ValueObject\CollectionName;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class UniqueCollectionNameGenerator implements UniqueCollectionNameGeneratorInterface {
  public function __construct(
    private CollectionRepositoryInterface $collectionRepository,
  ) {
  }

  public function generate(CollectionName $baseName, ID $userId): CollectionName {
    $baseNameValue = $baseName->toString();
    $existingNames = $this->collectionRepository->findNamesByPatternAndUser($baseNameValue, $userId->toString());

    if (empty($existingNames)) {
      return $baseName;
    }

    $maxSuffix = 0;
    $exactMatch = false;
    $pattern = '/^' . preg_quote($baseNameValue, '/') . ' \((\d+)\)$/';

    foreach ($existingNames as $name) {
      if ($name === $baseNameValue) {
        $exactMatch = true;
        continue;
      }

      if (preg_match($pattern, $name, $matches)) {
        $maxSuffix = max($maxSuffix, (int) $matches[1]);
      }
    }

    if (!$exactMatch) {
      return $baseName;
    }

    return CollectionName::fromString(sprintf('%s (%d)', $baseNameValue, $maxSuffix + 1));
  }
}
