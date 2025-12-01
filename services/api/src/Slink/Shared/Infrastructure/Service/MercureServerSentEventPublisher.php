<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Service;

use Slink\Shared\Domain\Service\ServerSentEventPublisherInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

#[AsAlias(ServerSentEventPublisherInterface::class)]
final readonly class MercureServerSentEventPublisher implements ServerSentEventPublisherInterface {
  public function __construct(
    private HubInterface $hub,
  ) {
  }

  public function publish(string $topic, array $data): void {
    $update = new Update(
      $topic,
      json_encode($data, JSON_THROW_ON_ERROR),
    );

    $this->hub->publish($update);
  }
}
