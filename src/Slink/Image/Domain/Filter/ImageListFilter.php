<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Filter;

final readonly class ImageListFilter {
  public function __construct(
    private ?int $limit = 10,
    private ?bool $isPublic = true,
    private ?string $orderBy = 'attributes.createdAt',
    private ?string $order = 'desc',
  ) {
  }
  
  public function getLimit(): ?int {
    return $this->limit;
  }
  
  public function getIsPublic(): ?bool {
    return $this->isPublic;
  }
  
  public function getOrderBy(): ?string {
    return $this->orderBy;
  }
  
  public function getOrder(): ?string {
    return $this->order;
  }
}