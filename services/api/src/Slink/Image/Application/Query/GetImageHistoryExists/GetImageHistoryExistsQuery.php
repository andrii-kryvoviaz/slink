<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageHistoryExists;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetImageHistoryExistsQuery implements QueryInterface {
  use EnvelopedMessage;

  /**
   * @param array<string>|null $tagIds
   */
  public function __construct(
    private ?string $searchTerm = null,
    private ?string $searchBy = null,
    private ?array  $tagIds = [],
    private bool    $requireAllTags = false,
  ) {
  }

  public function getSearchTerm(): ?string {
    return $this->searchTerm;
  }

  public function getSearchBy(): ?string {
    return $this->searchBy;
  }

  /**
   * @return array<string>|null
   */
  public function getTagIds(): ?array {
    return $this->tagIds;
  }

  public function requireAllTags(): bool {
    return $this->requireAllTags;
  }
}
