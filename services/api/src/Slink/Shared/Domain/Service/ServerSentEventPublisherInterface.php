<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Service;

interface ServerSentEventPublisherInterface {
  public function publish(string $topic, array $data): void;
}
