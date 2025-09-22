<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageList;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetImageListQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    private int     $limit = 10,
    private string  $orderBy = 'attributes.createdAt',
    private string  $order = 'desc',
    private ?string $searchTerm = null,
    private ?string $searchBy = null,
    private ?string $cursor = null,
    private ?array  $tagIds = [],
    private bool    $requireAllTags = false,
  ) {
  }

  public function getCursor(): ?string {
    return $this->cursor;
  }

  public function getLimit(): int {
    return $this->limit;
  }

  public function getOrder(): string {
    return $this->order;
  }

  public function getOrderBy(): string {
    return $this->orderBy;
  }

  public function getSearchBy(): ?string {
    return $this->searchBy;
  }

  public function getSearchTerm(): ?string {
    return $this->searchTerm;
  }

  public function getTagIds(): ?array {
    return $this->tagIds;
  }

  public function requireAllTags(): bool {
    return $this->requireAllTags;
  }
}