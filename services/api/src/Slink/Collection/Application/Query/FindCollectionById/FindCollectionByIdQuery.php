<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Query\FindCollectionById;

use Slink\Shared\Application\Query\QueryInterface;

final readonly class FindCollectionByIdQuery implements QueryInterface {
  public function __construct(
    private string $id,
  ) {
  }

  public function getId(): string {
    return $this->id;
  }
}
