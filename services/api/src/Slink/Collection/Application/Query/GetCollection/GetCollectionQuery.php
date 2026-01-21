<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Query\GetCollection;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetCollectionQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    private string $id,
  ) {
  }

  public function getId(): string {
    return $this->id;
  }
}
