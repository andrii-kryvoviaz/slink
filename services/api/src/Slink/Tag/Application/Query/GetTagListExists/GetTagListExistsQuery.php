<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Query\GetTagListExists;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class GetTagListExistsQuery implements QueryInterface {
  use EnvelopedMessage;

  /**
   * @param array<string>|null $ids
   */
  public function __construct(
    #[Assert\Uuid]
    private ?string $parentId = null,

    #[Assert\Length(max: 255)]
    private ?string $searchTerm = null,

    private ?bool   $rootOnly = null,

    #[Assert\All([
      new Assert\Uuid()
    ])]
    #[Assert\Count(max: 100)]
    private ?array  $ids = null,
  ) {
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

  /**
   * @return array<string>|null
   */
  public function getIds(): ?array {
    return $this->ids;
  }
}
