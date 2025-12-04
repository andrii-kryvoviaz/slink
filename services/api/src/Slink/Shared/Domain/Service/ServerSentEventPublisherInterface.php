<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Service;

interface ServerSentEventPublisherInterface {
  /**
   * @param array<string, mixed> $data
   */
  public function publish(string $topic, array $data): void;
}
