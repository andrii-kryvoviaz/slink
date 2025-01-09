<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Filter;

final readonly class ImageListFilter {
  /**
   * @param int|null $limit
   * @param string|null $orderBy
   * @param string|null $order
   * @param bool|null $isPublic
   * @param string|null $userId
   * @param array<string>|null $uuids
   */
  public function __construct(
    private ?int $limit = 10,
    private ?string $orderBy = 'attributes.createdAt',
    private ?string $order = 'desc',
    private ?bool $isPublic = null,
    private ?string $userId = null,
    private ?array $uuids = []
  ) {
  }
  
  /**
   * @return int|null
   */
  public function getLimit(): ?int {
    return $this->limit;
  }
  
  /**
   * @return string|null
   */
  public function getOrderBy(): ?string {
    return $this->orderBy;
  }
  
  /**
   * @return string|null
   */
  public function getOrder(): ?string {
    return $this->order;
  }
  
  /**
   * @return bool|null
   */
  public function getIsPublic(): ?bool {
    return $this->isPublic;
  }
  
  /**
   * @return string|null
   */
  public function getUserId(): ?string {
    return $this->userId;
  }
  
  /**
   * @return array<string>|null
   */
  public function getUuids(): ?array {
    return $this->uuids;
  }
}