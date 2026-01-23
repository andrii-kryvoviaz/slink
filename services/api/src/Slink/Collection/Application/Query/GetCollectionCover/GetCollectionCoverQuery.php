<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Query\GetCollectionCover;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetCollectionCoverQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    private string $id,
  ) {
  }

  public function getId(): string {
    return $this->id;
  }
}
