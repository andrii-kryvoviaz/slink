<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Concurrency;

use Doctrine\ORM\Events;
use Slink\Shared\Infrastructure\MessageBus\Command\MessengerCommandBus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Tests\Integration\Http\Double\FlushInterleaver;
use Tests\Integration\Http\Double\ViewCountInterleavingCommandBus;
use Tests\Integration\Http\HttpTestCase;
use Tests\Integration\Http\Image\DrivesFeedDelivery;
use Tests\Integration\Http\Store\BootsIndependentKernels;

abstract class ConcurrentRequestTestCase extends HttpTestCase {
  use BootsIndependentKernels;
  use DrivesFeedDelivery;

  private ViewCountInterleavingCommandBus $viewCountBus;
  private ?FlushInterleaver $flushInterleaver = null;
  private ?\Throwable $interleavedFailure = null;

  protected function setUp(): void {
    parent::setUp();

    $this->recordFeedPublications();

    $container = static::getContainer();
    /** @var MessageBusInterface $messageBus */
    $messageBus = $container->get('messenger.bus.command');
    $this->viewCountBus = new ViewCountInterleavingCommandBus(new MessengerCommandBus($messageBus));
    $container->set(MessengerCommandBus::class, $this->viewCountBus);
  }

  protected function tearDown(): void {
    $this->shutDownIndependentKernels();

    parent::tearDown();
  }

  protected function assertPostConditions(): void {
    self::assertTrue($this->flushInterleaver?->hasRun() ?? true, 'The request never reached its flush.');
    self::assertFalse($this->viewCountBus->hasPendingWrite(), 'The request never reached its view count.');
  }

  protected function onNotSuccessfulTest(\Throwable $failure): never {
    throw $this->interleavedFailure ?? $failure;
  }

  /**
   * @param array<string, string> $server
   */
  #[\Override]
  protected function apiRequest(
    string $method,
    string $path,
    ?string $token = null,
    array $server = [],
    ?string $body = null,
  ): int {
    $this->entityManager()->clear();

    return parent::apiRequest($method, $path, $token, $server, $body);
  }

  protected function beforeTheNextFlush(\Closure $write): void {
    $this->flushInterleaver = new FlushInterleaver($this->recordingFailure($write));
    $this->entityManager()->getEventManager()->addEventListener([Events::preFlush], $this->flushInterleaver);
  }

  protected function beforeTheNextViewCount(\Closure $write): void {
    $this->viewCountBus->beforeNextViewCount($this->recordingFailure($write));
  }

  private function recordingFailure(\Closure $write): \Closure {
    return function () use ($write): void {
      try {
        $write();
      } catch (\Throwable $failure) {
        $this->interleavedFailure = $failure;

        throw $failure;
      }
    };
  }

  /**
   * @param array<mixed>|null $body
   * @return array{status: int, body: string}
   */
  protected function requestFromAnotherKernel(string $method, string $path, ?string $token = null, ?array $body = null): array {
    $server = ['CONTENT_TYPE' => 'application/json'];

    if ($token !== null) {
      $server['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    }

    $content = null;

    if ($body !== null) {
      $content = \json_encode($body, JSON_THROW_ON_ERROR);
    }

    $response = $this->independentKernel()->handle(Request::create($path, $method, [], [], [], $server, $content));

    \ob_start();
    $response->sendContent();
    $responseBody = (string) \ob_get_clean();

    return ['status' => $response->getStatusCode(), 'body' => $responseBody];
  }

  /**
   * @param array<mixed> $body
   */
  protected function sendJson(string $method, string $path, ?string $token, array $body): int {
    return $this->apiRequest(
      $method,
      $path,
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode($body, JSON_THROW_ON_ERROR),
    );
  }

  protected function imageColumn(string $imageId, string $column): mixed {
    return $this->connection()->fetchOne(
      \sprintf('SELECT %s FROM "image" WHERE uuid = ?', $column),
      [$imageId],
    );
  }

  protected function responseBytes(): string {
    return (string) $this->client->getInternalResponse()->getContent();
  }
}
