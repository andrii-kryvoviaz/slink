<?php

declare(strict_types=1);

namespace Slink\Share\Application\Query\FindShareByShareable;

use Slink\Share\Domain\Enum\ShareableType;
use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class FindShareByShareableQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    private string $shareableId,
    private ShareableType $shareableType,
  ) {
  }

  public function getShareableId(): string {
    return $this->shareableId;
  }

  public function getShareableType(): ShareableType {
    return $this->shareableType;
  }
}
