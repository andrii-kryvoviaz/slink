<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Query\GetTagList;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class GetTagListQuery implements QueryInterface {
  use EnvelopedMessage;

  /**
   * @param array<string>|null $ids
   */
  public function __construct(
    #[Assert\Range(min: 1, max: 100)]
    private ?int    $limit = 50,

    #[Assert\Choice(['name', 'path', 'createdAt', 'updatedAt'])]
    private ?string $orderBy = 'name',

    #[Assert\Choice(['asc', 'desc'])]
    private ?string $order = 'asc',

    #[Assert\Range(min: 1)]
    private ?int    $page = 1,

    #[Assert\Uuid]
    private ?string $parentId = null,

    #[Assert\Length(max: 255)]
    private ?string $searchTerm = null,

    private ?bool   $rootOnly = null,

    private ?bool   $includeChildren = false,

    #[Assert\All([
      new Assert\Uuid()
    ])]
    #[Assert\Count(max: 100)]
    private ?array  $ids = null,
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

  public function getPage(): ?int {
    return $this->page;
  }

  public function getParentId(): ?string {
    return $this->parentId;
  }

  public function getSearchTerm(): ?string {
    return $this->searchTerm;
  }

  public function isRootOnly(): ?bool {
    return $this->rootOnly;
  }

  public function shouldIncludeChildren(): ?bool {
    return $this->includeChildren;
  }

  /**
   * @return array<string>|null
   */
  public function getIds(): ?array {
    return $this->ids;
  }
}