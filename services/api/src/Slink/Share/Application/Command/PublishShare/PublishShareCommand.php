<?php

declare(strict_types=1);

namespace Slink\Share\Application\Command\PublishShare;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class PublishShareCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    private string $shareId,
  ) {}

  public function getShareId(): string {
    return $this->shareId;
  }
}
