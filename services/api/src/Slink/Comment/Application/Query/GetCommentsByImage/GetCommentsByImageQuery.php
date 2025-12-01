<?php

declare(strict_types=1);

namespace Slink\Comment\Application\Query\GetCommentsByImage;

use Slink\Shared\Application\Query\QueryInterface;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class GetCommentsByImageQuery implements QueryInterface {
  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private string $imageId,

    #[Assert\Positive]
    private int $page = 1,

    #[Assert\Positive]
    #[Assert\LessThanOrEqual(100)]
    private int $limit = 20,
  ) {
  }

  public function getImageId(): string {
    return $this->imageId;
  }

  public function getPage(): int {
    return $this->page;
  }

  public function getLimit(): int {
    return $this->limit;
  }
}
