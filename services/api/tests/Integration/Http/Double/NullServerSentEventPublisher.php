<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Double;

use Slink\Shared\Domain\Service\ServerSentEventPublisherInterface;

final class NullServerSentEventPublisher implements ServerSentEventPublisherInterface {
  /**
   * @param array<string, mixed> $data
   */
  #[\Override]
  public function publish(string $topic, array $data): void {
  }
}
