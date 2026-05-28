<?php

declare(strict_types=1);

namespace Slink\Share\Application\Query\ListShares;

use Slink\Share\Domain\Enum\ShareExpiryFilter;
use Slink\Share\Domain\Enum\ShareProtectionFilter;
use Slink\Share\Domain\Enum\ShareTypeFilter;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class ListSharesQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    private int $limit = 10,
    private string $orderBy = 'createdAt',
    private string $order = 'desc',
    private ?string $cursor = null,
    private ?string $searchTerm = null,
    private ?ShareExpiryFilter $expiry = null,
    private ?ShareProtectionFilter $protection = null,
    private ?ShareTypeFilter $type = null,
    private ?string $shareableId = null,
    private ?ShareableType $shareableType = null,
  ) {
  }

  public function getLimit(): int {
    return $this->limit;
  }

  public function getOrderBy(): string {
    return $this->orderBy;
  }

  public function getOrder(): string {
    return $this->order;
  }

  public function getCursor(): ?string {
    return $this->cursor;
  }

  public function getSearchTerm(): ?string {
    return $this->searchTerm;
  }

  public function getExpiry(): ?ShareExpiryFilter {
    return $this->expiry;
  }

  public function getProtection(): ?ShareProtectionFilter {
    return $this->protection;
  }

  public function getType(): ?ShareableType {
    return $this->type?->toShareableType();
  }

  public function getShareableId(): ?string {
    return $this->shareableId;
  }

  public function getShareableType(): ?ShareableType {
    return $this->shareableType;
  }
}
