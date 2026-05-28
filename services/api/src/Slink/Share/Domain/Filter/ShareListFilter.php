<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Filter;

use Slink\Share\Domain\Enum\ShareExpiryFilter;
use Slink\Share\Domain\Enum\ShareProtectionFilter;
use Slink\Share\Domain\Enum\ShareableType;

final readonly class ShareListFilter {
  public function __construct(
    private ?int $limit = 10,
    private ?string $orderBy = 'createdAt',
    private ?string $order = 'desc',
    private ?string $cursor = null,
    private ?string $searchTerm = null,
    private ?ShareExpiryFilter $expiry = null,
    private ?ShareProtectionFilter $protection = null,
    private ?ShareableType $type = null,
    private ?string $shareableId = null,
    private ?ShareableType $shareableType = null,
    private ?string $userId = null,
  ) {
  }

  public function getLimit(): ?int {
    return $this->limit;
  }

  public function getOrderBy(): ?string {
    return $this->orderBy;
  }

  public function getOrder(): ?string {
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
    return $this->type;
  }

  public function getShareableId(): ?string {
    return $this->shareableId;
  }

  public function getShareableType(): ?ShareableType {
    return $this->shareableType;
  }

  public function getUserId(): ?string {
    return $this->userId;
  }
}
