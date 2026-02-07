<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Query\GetTagChildren;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class GetTagChildrenQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\Uuid]
    private string $parentId,
  ) {
  }

  public function getParentId(): string {
    return $this->parentId;
  }
}
