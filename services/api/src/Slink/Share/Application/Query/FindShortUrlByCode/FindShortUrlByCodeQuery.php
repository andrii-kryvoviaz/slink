<?php

declare(strict_types=1);

namespace Slink\Share\Application\Query\FindShortUrlByCode;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class FindShortUrlByCodeQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    private string $code,
  ) {
  }

  public function getCode(): string {
    return $this->code;
  }
}
