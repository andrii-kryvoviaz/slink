<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetPublicImageContent;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetPublicImageContentQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    public string $imageId,
  ) {
  }
}
