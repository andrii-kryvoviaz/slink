<?php

declare(strict_types=1);

namespace Tests\Integration\Console\Boot;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Shared\Application\Boot\Step\EventStoreSchemaStep;
use Slink\Shared\Application\Boot\SubCommandRunner;
use Slink\Shared\Application\Service\ProjectMetadataExtractor;
use Slink\Shared\Infrastructure\DataMigration\DataMigrationRunner;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Tests\Integration\Http\HttpTestCase;

#[Group('message-queue')]
final class MessageQueueBootTest extends HttpTestCase {
  #[Test]
  public function bootProvisionsTheQueueBeforeDataProcessing(): void {
    $connection = $this->connection();
    $connection->executeStatement('DROP TABLE message_queue');
    $dataMigrations = $this->createMock(DataMigrationRunner::class);
    $dataMigrations->expects($this->once())->method('getPending')->willReturnCallback(static function () use ($connection): array {
      self::assertTrue($connection->createSchemaManager()->tablesExist(['message_queue']));

      return [];
    });
    static::getContainer()->set(DataMigrationRunner::class, $dataMigrations);

    try {
      [$status, $output] = $this->bootResult();

      self::assertSame(0, $status, $output);
      self::assertTrue($connection->createSchemaManager()->tablesExist(['message_queue']));
    } finally {
      $this->setupQueue();
    }
  }

  #[Test]
  public function setupFailureStopsBootBeforeAnyDataProcessing(): void {
    $connection = $this->connection();
    $connection->executeStatement('DROP TABLE message_queue');
    $connection->executeStatement('CREATE VIEW message_queue AS SELECT 1 AS id');
    $dataMigrations = $this->createMock(DataMigrationRunner::class);
    $dataMigrations->expects($this->never())->method('getPending');
    static::getContainer()->set(DataMigrationRunner::class, $dataMigrations);
    try {
      [$status, $output] = $this->bootResult();
      self::assertSame(1, $status, $output);
      self::assertStringContainsString('queue setup failed', $output);
    } finally {
      $connection->executeStatement('DROP VIEW message_queue');
      $this->setupQueue();
    }
  }

  #[Test]
  public function runtimeSendingDoesNotLazilyCreateTheQueue(): void {
    $connection = $this->connection();
    $connection->executeStatement('DROP TABLE message_queue');
    $failure = null;
    try {
      $this->transport('feed')->send(new Envelope(new \DateTimeImmutable('2026-01-02T03:04:05Z')));
    } catch (\Throwable $exception) {
      $failure = $exception;
    } finally {
      self::assertFalse($connection->createSchemaManager()->tablesExist(['message_queue']));
      $this->setupQueue();
    }
    self::assertNotNull($failure);
  }

  #[Test]
  public function repeatedSetupPreservesPendingFailedAndOtherQueueMessages(): void {
    $pending = new \DateTimeImmutable('2026-01-02T03:04:05Z');
    $failed = new \DateInterval('P3D');
    $other = new \DateTimeZone('Europe/Bratislava');
    $this->transport('feed')->send(new Envelope($pending));
    $this->transport('feed_failed')->send(new Envelope($failed));
    $otherQueue = $this->otherQueue();
    $otherQueue->send(new Envelope($other));
    $before = $this->queueRows();

    $this->setupQueue();
    $this->setupQueue();

    self::assertSame($before, $this->queueRows());
    self::assertEquals($pending, $this->receive($this->transport('feed')));
    self::assertEquals($failed, $this->receive($this->transport('feed_failed')));
    self::assertEquals($other, $this->receive($otherQueue));
    self::assertSame([], $this->queueRows());
  }

  #[Test]
  public function setupLeavesApplicationTablesAndLegacyEventStoreTransportUntouched(): void {
    $connection = $this->connection();
    $connection->executeStatement('CREATE TABLE IF NOT EXISTS queue_setup_sentinel (value TEXT NOT NULL)');
    $connection->executeStatement("INSERT INTO queue_setup_sentinel (value) VALUES ('keep application data')");
    $eventStore = static::getContainer()->get('doctrine.dbal.event_store_connection');
    self::assertInstanceOf(Connection::class, $eventStore);
    $before = $eventStore->createSchemaManager()->listTableNames();

    $this->setupQueue();

    self::assertSame('keep application data', $connection->fetchOne('SELECT value FROM queue_setup_sentinel'));
    self::assertSame($before, $eventStore->createSchemaManager()->listTableNames());
    self::assertFalse($eventStore->createSchemaManager()->tablesExist(['message_queue']));
  }

  private function connection(): Connection {
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);
    self::assertInstanceOf(EntityManagerInterface::class, $entityManager);

    return $entityManager->getConnection();
  }

  private function transport(string $name): TransportInterface {
    $transport = static::getContainer()->get('messenger.transport.' . $name);
    self::assertInstanceOf(TransportInterface::class, $transport);

    return $transport;
  }

  private function otherQueue(): TransportInterface {
    $factory = static::getContainer()->get('messenger.transport_factory');
    $serializer = static::getContainer()->get('messenger.default_serializer');
    self::assertInstanceOf(TransportFactoryInterface::class, $factory);
    self::assertInstanceOf(SerializerInterface::class, $serializer);

    return $factory->createTransport('doctrine://read_model', [
      'table_name' => 'message_queue',
      'queue_name' => 'another_queue',
      'auto_setup' => false,
    ], $serializer);
  }

  private function receive(TransportInterface $transport): object {
    $received = iterator_to_array($transport->get(), false);
    self::assertCount(1, $received);
    $transport->ack($received[0]);

    return $received[0]->getMessage();
  }

  /** @return list<array<string, mixed>> */
  private function queueRows(): array {
    return $this->connection()->fetchAllAssociative('SELECT * FROM message_queue ORDER BY id');
  }

  private function setupQueue(): void {
    [$status, $output] = $this->consoleResult(['command' => 'messenger:setup-transports', 'transport' => 'feed']);
    self::assertSame(0, $status, $output);
  }

  /** @return array{int, string} */
  private function bootResult(): array {
    $runner = static::getContainer()->get(SubCommandRunner::class);
    self::assertInstanceOf(SubCommandRunner::class, $runner);
    $path = $this->connection()->getParams()['path'] ?? null;
    self::assertIsString($path);
    static::getContainer()->set(EventStoreSchemaStep::class, new EventStoreSchemaStep($runner, $path . '.es_migrations.yaml'));
    $metadata = $this->createStub(ProjectMetadataExtractor::class);
    $metadata->method('version')->willReturn('test');
    static::getContainer()->set(ProjectMetadataExtractor::class, $metadata);

    $environment = getenv('APP_ENV');
    $debug = getenv('APP_DEBUG');
    putenv('APP_ENV=test');
    putenv('APP_DEBUG=1');
    try {
      return $this->consoleResult(['command' => 'slink:boot']);
    } finally {
      putenv($environment === false ? 'APP_ENV' : 'APP_ENV=' . $environment);
      putenv($debug === false ? 'APP_DEBUG' : 'APP_DEBUG=' . $debug);
    }
  }

  /**
   * @param array<string, string> $arguments
   * @return array{int, string}
   */
  private function consoleResult(array $arguments): array {
    self::assertNotNull(self::$kernel);
    $application = new Application(self::$kernel);
    $application->setAutoExit(false);
    $application->setCatchExceptions(false);
    $output = new BufferedOutput();
    $status = $application->run(new ArrayInput($arguments + ['--no-interaction' => true, '--verbose' => true]), $output);

    return [$status, $output->fetch()];
  }
}
