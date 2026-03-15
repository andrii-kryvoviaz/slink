<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Query\GetUserCollections;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class GetUserCollectionsQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    private int     $limit = 12,
    private ?string $cursor = null,

    #[Assert\Length(max: 255)]
    private ?string $searchTerm = null,
  ) {
  }

  public function getLimit(): int {
    return $this->limit;
  }

  public function getCursor(): ?string {
    return $this->cursor;
  }

  public function getSearchTerm(): ?string {
    return $this->searchTerm;
  }
}
