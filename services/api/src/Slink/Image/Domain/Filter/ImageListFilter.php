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
   * @param string|null $searchTerm
   * @param string|null $searchBy
   * @param string|null $cursor
   * @param TagFilterData|null $tagFilterData
   */
  public function __construct(
    private ?int    $limit = 10,
    private ?string $orderBy = 'attributes.createdAt',
    private ?string $order = 'desc',
    private ?bool   $isPublic = null,
    private ?string $userId = null,
    private ?array  $uuids = [],
    private ?string $searchTerm = null,
    private ?string $searchBy = null,
    private ?string $cursor = null,
    private ?TagFilterData $tagFilterData = null,
  ) {
  }

  /**
   * @return string|null
   */
  public function getCursor(): ?string {
    return $this->cursor;
  }

  /**
   * @return bool|null
   */
  public function getIsPublic(): ?bool {
    return $this->isPublic;
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
  public function getOrder(): ?string {
    return $this->order;
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
  public function getSearchBy(): ?string {
    return $this->searchBy;
  }

  /**
   * @return string|null
   */
  public function getSearchTerm(): ?string {
    return $this->searchTerm;
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

  /**
   * @return TagFilterData|null
   */
  public function getTagFilterData(): ?TagFilterData {
    return $this->tagFilterData;
  }
}