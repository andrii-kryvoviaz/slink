<?php

declare(strict_types=1);

namespace Slink\Share\Application\Query\FindShareByTargetUrl;

use Slink\Share\Domain\ValueObject\TargetPath;
use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class FindShareByTargetUrlQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    private TargetPath $targetPath,
  ) {
  }

  public function getTargetPath(): TargetPath {
    return $this->targetPath;
  }
}
