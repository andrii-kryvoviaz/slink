<?php

declare(strict_types=1);

namespace Slink\Bookmark\Application\Command\AddBookmark;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class AddBookmarkCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    public string $imageId,
  ) {
  }
}
