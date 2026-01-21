<?php

declare(strict_types=1);

namespace Slink\Share\Application\Command\CreateShare;

use Slink\Share\Domain\ValueObject\ShareParams;
use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class CreateShareCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    private ShareParams $params,
  ) {
  }

  public function getParams(): ShareParams {
    return $this->params;
  }
}
