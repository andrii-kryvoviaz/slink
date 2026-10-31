<?php

declare(strict_types=1);

namespace Tests\Integration\Console\Boot;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\User\Application\Command\CreateUser\CreateUserCommand;
use Slink\User\Application\Command\GrantRole\GrantRoleCommand;
use Slink\User\Domain\Enum\UserStatus;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1183')]
final class FailedMessageTransportTest extends HttpTestCase {
  use RunsBootCommand;
  use SeedsLegacyEventStore;

  private const int FINAL_DELIVERY = 3;

  protected function setUp(): void {
    parent::setUp();

    $this->seedLegacyStore();
    $this->useEnv(['ES_DATABASE_URL' => $this->legacyStoreUrl()]);
  }

  protected function tearDown(): void {
    $this->removeLegacyStore();

    parent::tearDown();
  }

  #[Test]
  public function aMessageThatFailsLandsInTheMainDatabaseAndIsRetriedFromThere(): void {
    $files = $this->legacyStoreFiles();
    $user = new CreateUserCommand('late-member@local.test', self::PASSWORD, 'latemember', 'latemember', UserStatus::Active);
    $userId = $user->getId()->toString();
    $this->transport('async')->send(new Envelope(
      new GrantRoleCommand($userId, 'ROLE_ADMIN'),
      [new RedeliveryStamp(self::FINAL_DELIVERY)],
    ));

    [$consumed, $consumeOutput] = $this->consoleResult([
      'command' => 'messenger:consume',
      'receivers' => ['async'],
      '--limit' => '1',
      '--time-limit' => '5',
      '--no-reset' => true,
    ]);

    self::assertSame(0, $consumed, $consumeOutput);
    self::assertTrue($this->connection()->createSchemaManager()->tablesExist(['messenger_messages']));
    $failed = $this->connection()->fetchFirstColumn("SELECT id FROM messenger_messages WHERE queue_name = 'failed'");
    self::assertCount(1, $failed);
    self::assertSame($files, $this->legacyStoreFiles());

    $this->commandBus()->handle($user);
    [$retried, $retryOutput] = $this->consoleResult([
      'command' => 'messenger:failed:retry',
      'id' => [(string) $failed[0]],
      '--force' => true,
    ]);

    self::assertSame(0, $retried, $retryOutput);
    self::assertSame('0', (string) $this->connection()->fetchOne('SELECT COUNT(*) FROM messenger_messages'));
    self::assertSame('1', (string) $this->connection()->fetchOne(
      "SELECT COUNT(*) FROM user_to_role WHERE user_id = ? AND role = 'ROLE_ADMIN'",
      [$userId],
    ));
  }

  #[Test]
  public function sendingDoesNotCreateTheFailedQueueTable(): void {
    $connection = $this->connection();
    self::assertTrue($connection->createSchemaManager()->tablesExist(['messenger_messages']));
    $connection->executeStatement('ALTER TABLE messenger_messages RENAME TO messenger_messages_parked');
    $failure = null;

    try {
      $this->transport('failed')->send(new Envelope(new \DateTimeImmutable('2026-01-02T03:04:05Z')));
    } catch (\Throwable $exception) {
      $failure = $exception;
    } finally {
      self::assertFalse($connection->createSchemaManager()->tablesExist(['messenger_messages']));
      $connection->executeStatement('ALTER TABLE messenger_messages_parked RENAME TO messenger_messages');
    }

    self::assertNotNull($failure);
  }

  private function transport(string $name): TransportInterface {
    $transport = static::getContainer()->get('messenger.transport.' . $name);
    self::assertInstanceOf(TransportInterface::class, $transport);

    return $transport;
  }
}
