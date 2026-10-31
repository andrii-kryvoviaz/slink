<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Image;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Slink\Shared\Infrastructure\Service\MercureServerSentEventPublisher;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\Integration\Http\Double\RecordingFeedPublisher;

trait DrivesFeedDelivery {
  private RecordingFeedPublisher $publisher;

  protected function recordFeedPublications(): void {
    $this->publisher = new RecordingFeedPublisher();
    static::getContainer()->set(MercureServerSentEventPublisher::class, $this->publisher);
  }

  protected function entityManager(): EntityManagerInterface {
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);
    self::assertInstanceOf(EntityManagerInterface::class, $entityManager);

    return $entityManager;
  }

  protected function connection(): Connection {
    return $this->entityManager()->getConnection();
  }

  protected function pending(): int {
    return (int) $this->connection()->fetchOne("SELECT COUNT(*) FROM message_queue WHERE queue_name = 'feed'");
  }

  protected function consumeOne(): void {
    self::assertGreaterThan(0, $this->pending());
    $this->console([
      'command' => 'messenger:consume',
      'receivers' => ['feed'],
      '--limit' => 1,
      '--no-interaction' => true,
    ]);
  }

  /** @param array<string, bool|int|string|list<string>> $arguments */
  protected function console(array $arguments): void {
    self::assertNotNull(self::$kernel);
    $application = new Application(self::$kernel);
    $application->setAutoExit(false);
    $output = new BufferedOutput();
    $status = $application->run(new ArrayInput($arguments), $output);
    self::assertSame(0, $status, $output->fetch());
  }

  protected function assertPublished(string $event, string $imageId): void {
    $message = array_last($this->publisher->messages);
    self::assertNotNull($message);
    self::assertSame('public-feed', $message['topic']);
    self::assertFalse($message['private']);
    self::assertSame($event, $message['data']['event']);
    if ($event === 'image_removed') {
      self::assertSame($imageId, $message['data']['imageId']);
      self::assertSame(['event', 'imageId'], array_keys($message['data']));
      return;
    }
    self::assertSame(['event', 'image'], array_keys($message['data']));
    self::assertSame($imageId, $message['data']['image']['id']);
  }
}
