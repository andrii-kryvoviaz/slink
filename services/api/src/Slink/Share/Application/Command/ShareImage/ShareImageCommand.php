<?php

declare(strict_types=1);

namespace Slink\Share\Application\Command\ShareImage;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class ShareImageCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    private string $imageId,
    private string $targetUrl,
    private bool $createShortUrl = true,
  ) {
  }

  public function getImageId(): string {
    return $this->imageId;
  }

  public function getTargetUrl(): string {
    return $this->targetUrl;
  }

  public function shouldCreateShortUrl(): bool {
    return $this->createShortUrl;
  }
}
