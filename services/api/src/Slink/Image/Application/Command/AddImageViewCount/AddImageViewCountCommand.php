<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\AddImageViewCount;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class AddImageViewCountCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(private string $id) {
  }
  
  public function getId(): string {
    return $this->id;
  }
}