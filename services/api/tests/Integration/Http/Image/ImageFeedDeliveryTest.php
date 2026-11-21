<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Image;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Infrastructure\Service\MercureServerSentEventPublisher;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Tests\Integration\Http\HttpTestCase;

#[Group('feed-delivery')]
final class ImageFeedDeliveryTest extends HttpTestCase {
  use DrivesFeedDelivery;

  private string $token;
  private string $ownerId;

  protected function setUp(): void {
    parent::setUp();
    $this->recordFeedPublications();
    $this->ownerId = $this->createUser('feed-owner@local.test', 'feedowner', self::PASSWORD);
    $this->token = $this->login('feedowner', self::PASSWORD);
  }

  #[Test]
  public function rolledBackPublicationNeverReachesThePublicFeed(): void {
    $imageId = $this->uploadImage($this->token, false);
    $entityManager = $this->entityManager();
    $connection = $entityManager->getConnection();

    $connection->beginTransaction();
    $status = $this->apiRequest(
      'PATCH',
      '/api/image/' . $imageId,
      $this->token,
      ['CONTENT_TYPE' => 'application/json'],
      json_encode(['isPublic' => true, 'description' => 'private pending caption'], JSON_THROW_ON_ERROR),
    );
    $connection->rollBack();

    self::assertContains($status, [200, 204]);
    self::assertSame([], $this->publisher->messages);
  }

  #[Test]
  public function publicWritesBecomeDurableBeforeTheWorkerPublishes(): void {
    $imageId = $this->uploadImage($this->token, true);
    self::assertSame([], $this->publisher->messages);
    self::assertSame(1, $this->pending());
    self::assertStringNotContainsString('sample.png', (string) $this->connection()->fetchOne('SELECT body FROM message_queue'));

    $this->consumeOne();
    $this->assertPublished('image_added', $imageId);
    self::assertSame(0, $this->pending());

    $this->change($imageId, true, 'current caption');
    self::assertStringNotContainsString('current caption', (string) $this->connection()->fetchOne('SELECT body FROM message_queue'));
    $this->consumeOne();
    $this->assertPublished('image_updated', $imageId);
    self::assertStringContainsString('current caption', json_encode($this->publisher->messages, JSON_THROW_ON_ERROR));

    $this->change($imageId, false);
    $this->consumeOne();
    $this->assertPublished('image_removed', $imageId);
    $this->change($imageId, true);
    $this->consumeOne();
    $this->assertPublished('image_added', $imageId);
    self::assertContains($this->apiRequest('DELETE', '/api/image/' . $imageId, $this->token), [200, 204]);
    $this->consumeOne();
    $this->assertPublished('image_removed', $imageId);
  }

  #[Test]
  public function privateWritesDoNotQueuePublicNotifications(): void {
    $imageId = $this->uploadImage($this->token, false);
    $this->change($imageId, false, 'private caption');
    self::assertContains($this->apiRequest('DELETE', '/api/image/' . $imageId, $this->token), [200, 204]);
    self::assertSame(0, $this->pending());
    self::assertSame([], $this->publisher->messages);
  }

  #[Test]
  public function addedImagesUseThePublicListingShapeAndAWorkingContentUrl(): void {
    $this->setAccessSettings(['allowUnauthenticatedAccess' => true]);
    $imageId = $this->uploadImage($this->token, true);
    self::assertSame(200, $this->apiRequest('GET', '/api/images'));
    $listing = json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $listedImage = $listing['data'][0];
    self::assertSame($imageId, $listedImage['id']);

    $this->consumeOne();
    $this->assertPublished('image_added', $imageId);
    $message = array_last($this->publisher->messages);
    self::assertNotNull($message);
    $image = $message['data']['image'];
    self::assertArrayHasKey('url', $image);
    self::assertSame('/api/image/public/' . $imageId . '.png', $image['url']);
    self::assertArrayHasKey('createdAt', $image['attributes']);
    self::assertSame('image/png', $image['metadata']['mimeType']);
    self::assertArrayHasKey('bookmarkCount', $image);
    self::assertArrayNotHasKey('isBookmarked', $image);
    self::assertEquals(
      array_intersect_key($listedImage, array_flip(['id', 'owner', 'url', 'attributes', 'metadata', 'bookmarkCount'])),
      $image,
    );
    self::assertSame(200, $this->apiRequest('GET', $image['url']));
    self::assertSame('image/png', $this->client->getResponse()->headers->get('Content-Type'));
  }

  #[Test]
  public function feedWorkersLeaveOtherLogicalQueuesAndTheirPrivatePayloadsUntouched(): void {
    $factory = static::getContainer()->get('messenger.transport_factory');
    $serializer = static::getContainer()->get('messenger.default_serializer');
    self::assertInstanceOf(TransportFactoryInterface::class, $factory);
    self::assertInstanceOf(SerializerInterface::class, $serializer);
    $otherQueue = $factory->createTransport('doctrine://read_model', [
      'table_name' => 'message_queue',
      'queue_name' => 'private_tasks',
      'auto_setup' => false,
    ], $serializer);
    $otherQueue->send(new Envelope(new \ArrayObject(['privateMetadata' => 'unrelated secret payload'])));
    $before = $this->connection()->fetchAssociative("SELECT * FROM message_queue WHERE queue_name = 'private_tasks'");
    $imageId = $this->uploadImage($this->token, true);

    $this->consumeOne();

    $this->assertPublished('image_added', $imageId);
    self::assertCount(1, $this->publisher->messages);
    self::assertStringNotContainsString('unrelated secret payload', json_encode($this->publisher->messages, JSON_THROW_ON_ERROR));
    self::assertSame($before, $this->connection()->fetchAssociative("SELECT * FROM message_queue WHERE queue_name = 'private_tasks'"));
  }

  #[Test]
  public function anUnrelatedMessageSentToTheFeedQueueIsNeverPublished(): void {
    $transport = static::getContainer()->get('messenger.transport.feed');
    self::assertInstanceOf(TransportInterface::class, $transport);
    $transport->send(new Envelope(new \ArrayObject(['privateMetadata' => 'misrouted secret payload'])));

    $this->consumeOne();

    self::assertSame([], $this->publisher->messages);
    self::assertSame(1, (int) $this->connection()->fetchOne('SELECT COUNT(*) FROM message_queue'));
  }

  #[Test]
  public function nestedFlushesAreInvisibleToTheWorkerUntilTheOutermostCommit(): void {
    $imageId = $this->uploadImage($this->token, false);
    $connection = $this->connection();
    $connection->beginTransaction();
    $connection->beginTransaction();
    $this->change($imageId, true);
    $connection->commit();
    self::assertSame(1, $this->pending());
    self::assertSame(0, $this->visiblePending());
    self::assertSame([], $this->publisher->messages);
    $connection->commit();
    self::assertSame(1, $this->visiblePending());
    $this->consumeOne();
    $this->assertPublished('image_added', $imageId);
  }

  #[Test]
  public function savepointRollbackDiscardsWorkAndAllowsANewSuccessfulAttempt(): void {
    $imageId = $this->uploadImage($this->token, false);
    $connection = $this->connection();
    $connection->beginTransaction();
    $connection->beginTransaction();
    $this->change($imageId, true);
    $connection->rollBack();
    $connection->commit();
    self::assertSame(0, $this->pending());
    $this->entityManager()->clear();

    $image = $this->entityManager()->find(ImageView::class, $imageId);
    self::assertNotNull($image);
    $image->updateAttributes(ImageAttributes::create($image->getFileName(), 'retry committed', true));
    $this->entityManager()->flush();
    self::assertSame(1, $this->pending());
    self::assertSame([], $this->publisher->messages);
    $this->consumeOne();
    $this->assertPublished('image_added', $imageId);
  }

  #[Test]
  public function aLaterSqlFailureRollsBackTheImageAndItsQueuedNotification(): void {
    $imageId = $this->uploadImage($this->token, false);
    $connection = $this->connection();
    $connection->beginTransaction();
    $this->change($imageId, true);
    try {
      $connection->executeStatement('INSERT INTO image (uuid) VALUES (?)', [$imageId]);
      self::fail('Expected duplicate image write to fail.');
    } catch (\Doctrine\DBAL\Exception $exception) {
      self::assertNotSame('', $exception->getMessage());
      $connection->rollBack();
    }

    self::assertSame(0, $this->visiblePending());
    self::assertSame([], $this->publisher->messages);
    $this->entityManager()->clear();
    self::assertFalse($this->image($imageId)->getAttributes()->isPublic());
  }

  #[Test]
  public function aFinalCommitFailureDiscardsAnAlreadyFlushedNotification(): void {
    $imageId = $this->uploadImage($this->token, false);
    $connection = $this->connection();
    $connection->executeStatement('PRAGMA foreign_keys = ON');
    $connection->executeStatement('CREATE TABLE IF NOT EXISTS feed_commit_failure (id INTEGER PRIMARY KEY, parent INTEGER REFERENCES feed_commit_failure(id) DEFERRABLE INITIALLY DEFERRED)');
    $connection->beginTransaction();
    $this->change($imageId, true);
    $connection->executeStatement('INSERT INTO feed_commit_failure (id, parent) VALUES (1, 999)');
    try {
      $connection->commit();
      self::fail('Expected deferred foreign key failure at final commit.');
    } catch (\Doctrine\DBAL\Exception $exception) {
      self::assertStringContainsString('FOREIGN KEY', $exception->getMessage());
      $native = $connection->getNativeConnection();
      self::assertInstanceOf(\PDO::class, $native);
      $native->rollBack();
      $connection->close();
    }
    self::assertSame(0, $this->visiblePending());
    self::assertSame([], $this->publisher->messages);
  }

  #[Test]
  public function failureLaterInTheSameFlushDiscardsEarlierQueuedWork(): void {
    $firstId = $this->uploadImage($this->token, false);
    $secondId = $this->uploadImage($this->token, false);
    $entityManager = $this->entityManager();
    $connection = $this->connection();
    $connection->executeStatement('CREATE TRIGGER fail_second_feed_update BEFORE UPDATE ON image WHEN NEW.uuid = ' . $connection->quote($secondId) . " BEGIN SELECT RAISE(ABORT, 'injected second update failure'); END");
    $first = $this->image($firstId);
    $second = $this->image($secondId);
    $first->updateAttributes(ImageAttributes::create($first->getFileName(), 'first update', true));
    $second->updateAttributes(ImageAttributes::create($second->getFileName(), 'second update', true));
    try {
      $entityManager->flush();
      self::fail('Expected second image update to fail.');
    } catch (\Doctrine\DBAL\Exception $exception) {
      self::assertStringContainsString('injected second update failure', $exception->getMessage());
    } finally {
      $connection->executeStatement('DROP TRIGGER fail_second_feed_update');
    }
    self::assertSame(0, $this->visiblePending());
    self::assertSame([], $this->publisher->messages);
  }

  #[Test]
  #[DataProvider('nestedDeletionTransactions')]
  public function nestedDeletionNotificationsFollowTheirActualTransaction(bool $outerTransaction): void {
    $firstId = $this->uploadImage($this->token, true);
    $secondId = $this->uploadImage($this->token, true);
    $collectionId = $this->createCollection($this->token);
    $this->addImageToCollection($this->token, $collectionId, $firstId);
    $this->addImageToCollection($this->token, $collectionId, $secondId);
    $this->consumeOne();
    $this->consumeOne();
    $this->publisher->messages = [];
    $connection = $this->connection();
    $connection->executeStatement('CREATE TRIGGER fail_second_feed_delete BEFORE DELETE ON image WHEN (SELECT COUNT(*) FROM image WHERE uuid IN (' . $connection->quote($firstId) . ', ' . $connection->quote($secondId) . ")) = 1 BEGIN SELECT RAISE(ABORT, 'injected nested delete failure'); END");
    if ($outerTransaction) {
      $connection->beginTransaction();
    }
    try {
      $status = $this->apiRequest('DELETE', '/api/collection', $this->token,
        ['CONTENT_TYPE' => 'application/json'],
        json_encode(['id' => $collectionId, 'deleteImages' => true], JSON_THROW_ON_ERROR),
      );
      self::assertSame(500, $status);
      $visibleBeforeRollback = $this->visiblePending();
    } finally {
      if ($outerTransaction && $connection->isTransactionActive()) {
        $connection->rollBack();
      }
      $connection->executeStatement('DROP TRIGGER fail_second_feed_delete');
    }
    self::assertSame([], $this->publisher->messages);
    if ($outerTransaction) {
      self::assertSame(0, $visibleBeforeRollback);
      self::assertSame(2, (int) $connection->fetchOne('SELECT COUNT(*) FROM image WHERE uuid IN (?, ?)', [$firstId, $secondId]));
      self::assertSame(0, $this->visiblePending());
      return;
    }
    $remaining = $connection->fetchFirstColumn('SELECT uuid FROM image WHERE uuid IN (?, ?)', [$firstId, $secondId]);
    $removed = array_values(array_diff([$firstId, $secondId], $remaining));
    self::assertCount(1, $removed);
    self::assertSame(1, $this->visiblePending());
    $this->restartKernel();
    $this->consumeOne();
    $this->assertPublished('image_removed', $removed[0]);
  }

  /** @return array<string, array{bool}> */
  public static function nestedDeletionTransactions(): array {
    return ['independent nested commits' => [false], 'explicit outer rollback' => [true]];
  }

  #[Test]
  public function aFiniteQueueClaimDoesNotAbortAnUnrelatedUserCommand(): void {
    if (!function_exists('proc_open')) {
      self::markTestSkipped('Queue concurrency coverage requires process support.');
    }
    $imageId = $this->uploadImage($this->token, true);
    $path = $this->connection()->getParams()['path'] ?? null;
    self::assertIsString($path);
    $listener = new FiniteQueueWriter($path);
    $events = $this->entityManager()->getEventManager();
    $events->addEventListener([\Doctrine\ORM\Events::onFlush], $listener);
    $failure = null;
    try {
      $this->createUser('concurrent@local.test', 'concurrentuser', self::PASSWORD);
    } catch (\Throwable $exception) {
      $failure = $exception;
    } finally {
      $events->removeEventListener([\Doctrine\ORM\Events::onFlush], $listener);
      $result = $listener->finish();
    }
    self::assertNull($failure, $failure?->getMessage() ?? '');
    self::assertSame(0, $result['status'], $result['error']);
    self::assertStringContainsString('committed', $result['output']);
    self::assertSame(1, (int) $this->connection()->fetchOne('SELECT COUNT(*) FROM "user" WHERE email = ?', ['concurrent@local.test']));
    $this->consumeOne();
    $this->assertPublished('image_added', $imageId);
  }

  #[Test]
  public function aBackloggedPublicationCannotExposeAnImageThatIsNowPrivate(): void {
    $imageId = $this->uploadImage($this->token, true);
    $this->change($imageId, false, 'now private caption');
    $this->consumeOne();
    $this->assertPublished('image_removed', $imageId);
    $this->consumeOne();
    $this->assertPublished('image_removed', $imageId);
    self::assertStringNotContainsString('caption', json_encode($this->publisher->messages, JSON_THROW_ON_ERROR));
  }

  #[Test]
  public function aBackloggedPublicationCannotExposeAnImageThatWasDeleted(): void {
    $imageId = $this->uploadImage($this->token, true);
    self::assertContains($this->apiRequest('DELETE', '/api/image/' . $imageId, $this->token), [200, 204]);
    $this->consumeOne();
    $this->assertPublished('image_removed', $imageId);
    $this->consumeOne();
    $this->assertPublished('image_removed', $imageId);
  }

  #[Test]
  public function queuedImagesFromADeletedOwnerAreRemovedWithoutMetadata(): void {
    $imageId = $this->uploadImage($this->token, true);
    $this->softDelete($this->bootAdmin(), $this->ownerId);
    $this->consumeOne();
    $this->assertPublished('image_removed', $imageId);
  }

  #[Test]
  public function anOldRemovalPublishesTheCurrentPublicImageInsteadOfRemovingIt(): void {
    $imageId = $this->uploadImage($this->token, true);
    $this->consumeOne();
    $this->change($imageId, false);
    $this->change($imageId, true, 'public again');
    $this->consumeOne();
    $this->assertPublished('image_updated', $imageId);
    self::assertStringContainsString('public again', json_encode($this->publisher->messages, JSON_THROW_ON_ERROR));
    $this->consumeOne();
    $this->assertPublished('image_added', $imageId);
  }

  #[Test]
  public function mercureFailureDoesNotFailTheWriteAndDeliveryCanRetry(): void {
    $this->publisher->fail = true;
    $imageId = $this->uploadImage($this->token, true);
    self::assertSame(1, $this->pending());
    $this->consumeOne();
    self::assertSame(1, $this->pending());
    self::assertSame([], $this->publisher->messages);
    $this->publisher->fail = false;
    $this->makeDeliveryDue();
    $this->consumeOne();
    $this->assertPublished('image_added', $imageId);
    self::assertSame(0, $this->pending());
  }

  #[Test]
  public function exhaustedRetriesRemainDurableAndInspectable(): void {
    $this->publisher->fail = true;
    $imageId = $this->uploadImage($this->token, true);
    for ($attempt = 0; $attempt < 6; $attempt++) {
      $this->makeDeliveryDue();
      $this->consumeOne();
    }
    self::assertSame(0, $this->pending());
    self::assertSame(1, (int) $this->connection()->fetchOne("SELECT COUNT(*) FROM message_queue WHERE queue_name = 'feed_failed'"));
    self::assertStringContainsString('Mercure unavailable', (string) $this->connection()->fetchOne("SELECT body FROM message_queue WHERE queue_name = 'feed_failed'"));
    self::assertSame([], $this->publisher->messages);
    $this->publisher->fail = false;
    $this->console([
      'command' => 'messenger:failed:retry',
      'id' => [(string) $this->connection()->fetchOne("SELECT id FROM message_queue WHERE queue_name = 'feed_failed'")],
      '--transport' => 'feed_failed',
      '--force' => true,
      '--no-interaction' => true,
    ]);
    $this->assertPublished('image_added', $imageId);
    self::assertSame(0, (int) $this->connection()->fetchOne("SELECT COUNT(*) FROM message_queue WHERE queue_name = 'feed_failed'"));
  }

  #[Test]
  public function anOlderRetryReadsCurrentPrivacyAndCannotRestoreOldMetadata(): void {
    $imageId = $this->uploadImage($this->token, true);
    $this->publisher->fail = true;
    $this->consumeOne();
    $this->change($imageId, false, 'private after first failure');
    $this->publisher->fail = false;
    $this->makeDeliveryDue();
    $this->consumeOne();
    $this->consumeOne();
    $this->assertPublished('image_removed', $imageId);
    self::assertStringNotContainsString('private after first failure', json_encode($this->publisher->messages, JSON_THROW_ON_ERROR));
  }

  #[Test]
  public function aWorkerRefreshesManagedImagesBeforePublishingANewerDelivery(): void {
    $imageId = $this->uploadImage($this->token, true);
    $image = $this->entityManager()->find(ImageView::class, $imageId);
    self::assertNotNull($image);
    self::assertTrue($image->getAttributes()->isPublic());
    $otherWriter = $this->independentConnection();
    $statement = $otherWriter->prepare('UPDATE image SET is_public = 0, description = ? WHERE uuid = ?');
    $statement->execute(['private in another process', $imageId]);
    self::assertTrue($image->getAttributes()->isPublic());

    $this->consumeOne();
    $this->assertPublished('image_removed', $imageId);
  }

  #[Test]
  public function killingTheWriterBeforeCommitLeavesNoImageChangeOrFeedWork(): void {
    $imageId = $this->uploadImage($this->token, false);
    $this->killProcessDuring(function () use ($imageId): void {
      $this->connection()->beginTransaction();
      $this->change($imageId, true, 'never committed');
      posix_kill(posix_getpid(), SIGKILL);
    });

    self::assertSame(0, $this->pending());
    $this->entityManager()->clear();
    self::assertFalse($this->image($imageId)->getAttributes()->isPublic());
    self::assertSame([], $this->publisher->messages);
  }

  #[Test]
  public function killingTheWriterAfterCommitLeavesDeliveryAvailableToAnotherWorker(): void {
    $imageId = $this->uploadImage($this->token, false);
    $this->killProcessDuring(function () use ($imageId): void {
      $this->change($imageId, true, 'committed before crash');
      posix_kill(posix_getpid(), SIGKILL);
    });

    self::assertSame(1, $this->pending());
    self::assertSame([], $this->publisher->messages);
    $this->consumeOne();
    $this->assertPublished('image_added', $imageId);
    self::assertStringContainsString('committed before crash', json_encode($this->publisher->messages, JSON_THROW_ON_ERROR));
  }

  #[Test]
  public function killingTheWorkerAfterPublicationBeforeAcknowledgementRedeliversTheSameNotice(): void {
    $imageId = $this->uploadImage($this->token, true);
    $queuedBody = $this->connection()->fetchOne("SELECT body FROM message_queue WHERE queue_name = 'feed'");
    $journal = tempnam(sys_get_temp_dir(), 'feed-accepted-');
    self::assertNotFalse($journal);
    try {
      $this->killProcessDuring(function () use ($journal): void {
        $this->publisher->journal = $journal;
        $this->publisher->killAfterPublish = true;
        $this->consumeOne();
      });
      $accepted = json_decode((string) file_get_contents($journal), true, 512, JSON_THROW_ON_ERROR);
      self::assertSame($imageId, $accepted['data']['image']['id']);
      self::assertSame(1, $this->pending());
      self::assertSame($queuedBody, $this->connection()->fetchOne("SELECT body FROM message_queue WHERE queue_name = 'feed'"));
      $this->connection()->executeStatement("UPDATE message_queue SET delivered_at = '2000-01-01 00:00:00' WHERE queue_name = 'feed'");
      $this->consumeOne();
      $this->assertPublished('image_added', $imageId);
      self::assertSame($accepted['data'], $this->publisher->messages[0]['data']);
      self::assertSame(0, $this->pending());
    } finally {
      unlink($journal);
    }
  }

  #[Test]
  public function frameworkSetupCreatesTheGenericMessageQueue(): void {
    $this->console([
      'command' => 'messenger:setup-transports',
      'transport' => 'feed',
    ]);

    self::assertTrue($this->connection()->createSchemaManager()->tablesExist(['message_queue']));
  }

  #[Test]
  public function frameworkProvisioningCreatesTheQueueAndRetryPreservesPendingDelivery(): void {
    $imageId = $this->uploadImage($this->token, false);
    $this->removeQueue();
    self::assertFalse($this->connection()->createSchemaManager()->tablesExist(['message_queue']));
    $this->setupQueue();
    $this->change($imageId, true);
    self::assertSame(1, $this->pending());
    $this->setupQueue();
    self::assertSame(1, $this->pending());
    $this->consumeOne();
    $this->assertPublished('image_added', $imageId);
  }

  #[Test]
  public function frameworkRepairsAMissingLeaseColumnAndIndexWithoutDroppingMessages(): void {
    $imageId = $this->uploadImage($this->token, true);
    $connection = $this->connection();
    $schema = $connection->createSchemaManager();
    foreach ($schema->listTableIndexes('message_queue') as $index) {
      if (!$index->isPrimary()) {
        $connection->executeStatement($connection->getDatabasePlatform()->getDropIndexSQL($index->getName(), 'message_queue'));
      }
    }
    $connection->executeStatement('ALTER TABLE message_queue DROP COLUMN delivered_at');
    $this->setupQueue();
    self::assertSame(1, $this->pending());
    self::assertArrayHasKey('delivered_at', $schema->listTableColumns('message_queue'));
    self::assertGreaterThan(1, count($schema->listTableIndexes('message_queue')));
    $this->consumeOne();
    $this->assertPublished('image_added', $imageId);
  }

  #[Test]
  public function setupKilledInsideASqliteTransactionCanBeRetried(): void {
    $this->removeQueue();
    $this->killProcessDuring(function (): void {
      $this->connection()->beginTransaction();
      $this->setupQueue();
      posix_kill(posix_getpid(), SIGKILL);
    });
    self::assertFalse($this->connection()->createSchemaManager()->tablesExist(['message_queue']));
    $this->setupQueue();
    self::assertTrue($this->connection()->createSchemaManager()->tablesExist(['message_queue']));
    $imageId = $this->uploadImage($this->token, true);
    $this->consumeOne();
    $this->assertPublished('image_added', $imageId);
  }

  #[Test]
  public function setupKilledAfterCommitRestartsWithoutLosingTheQueue(): void {
    $this->removeQueue();
    $this->killProcessDuring(function (): void {
      $this->setupQueue();
      posix_kill(posix_getpid(), SIGKILL);
    });
    self::assertTrue($this->connection()->createSchemaManager()->tablesExist(['message_queue']));
    $this->setupQueue();
    $imageId = $this->uploadImage($this->token, true);
    $this->consumeOne();
    $this->assertPublished('image_added', $imageId);
  }

  private function removeQueue(): void {
    $this->connection()->executeStatement('DROP TABLE message_queue');
    $this->restartKernel();
  }

  private function restartKernel(): void {
    self::ensureKernelShutdown();
    $this->client = static::createClient();
    $this->client->disableReboot();
    $this->client->catchExceptions(true);
    static::getContainer()->set(MercureServerSentEventPublisher::class, $this->publisher);
  }

  private function setupQueue(): void {
    $this->console(['command' => 'messenger:setup-transports', 'transport' => 'feed', '--no-interaction' => true]);
  }

  private function killProcessDuring(\Closure $operation): void {
    if (!function_exists('pcntl_fork') || !function_exists('posix_kill')) {
      self::markTestSkipped('Process crash coverage requires pcntl and posix.');
    }
    $this->connection()->close();
    $pid = pcntl_fork();
    self::assertNotSame(-1, $pid);
    if ($pid === 0) {
      try {
        $operation();
      } catch (\Throwable $exception) {
        fwrite(STDERR, $exception->getMessage() . "\n");
      }
      exit(73);
    }
    pcntl_waitpid($pid, $status);
    self::assertTrue(pcntl_wifsignaled($status), 'Child did not stop at the injected crash boundary.');
    self::assertSame(SIGKILL, pcntl_wtermsig($status));
  }

  private function change(string $imageId, bool $public, string $description = 'feed caption'): void {
    $status = $this->apiRequest('PATCH', '/api/image/' . $imageId, $this->token,
      ['CONTENT_TYPE' => 'application/json'],
      json_encode(['isPublic' => $public, 'description' => $description], JSON_THROW_ON_ERROR),
    );
    self::assertContains($status, [200, 204], (string) $this->client->getResponse()->getContent());
  }

  private function image(string $imageId): ImageView {
    $image = $this->entityManager()->find(ImageView::class, $imageId);
    self::assertNotNull($image);

    return $image;
  }

  private function independentConnection(): \PDO {
    $path = $this->connection()->getParams()['path'] ?? null;
    self::assertIsString($path);

    return new \PDO('sqlite:' . $path);
  }

  private function visiblePending(): int {
    $pdo = $this->independentConnection();

    $statement = $pdo->query("SELECT COUNT(*) FROM message_queue WHERE queue_name = 'feed'");
    self::assertNotFalse($statement);

    return (int) $statement->fetchColumn();
  }

  private function makeDeliveryDue(): void {
    $this->connection()->executeStatement("UPDATE message_queue SET available_at = '2000-01-01 00:00:00', delivered_at = NULL WHERE queue_name = 'feed'");
  }
}

final class FiniteQueueWriter {
  /** @var resource|null */
  private $process = null;
  /** @var resource|null */
  private $output = null;
  /** @var resource|null */
  private $errors = null;

  public function __construct(private readonly string $databasePath) {
  }

  public function onFlush(): void {
    if ($this->process !== null) {
      return;
    }
    $script = <<<'PHP'
      $database = new PDO('sqlite:' . $argv[1]);
      $database->exec('PRAGMA busy_timeout = 2000');
      $database->beginTransaction();
      $database->exec("UPDATE message_queue SET delivered_at = '2000-01-01 00:00:00' WHERE queue_name = 'feed'");
      fwrite(STDOUT, "claimed\n");
      fflush(STDOUT);
      $database->commit();
      fwrite(STDOUT, "committed\n");
      PHP;
    $process = proc_open([PHP_BINARY, '-r', $script, $this->databasePath], [
      0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w'],
    ], $pipes);
    if (!is_resource($process)) {
      throw new \RuntimeException('Unable to start the independent queue writer.');
    }
    $this->process = $process;
    fclose($pipes[0]);
    $this->output = $pipes[1];
    $this->errors = $pipes[2];
    stream_set_timeout($this->output, 5);
    if (trim((string) fgets($this->output)) !== 'claimed') {
      throw new \RuntimeException('Independent queue writer did not acquire its write lock.');
    }
  }

  /** @return array{status: int, output: string, error: string} */
  public function finish(): array {
    if (!is_resource($this->process) || !is_resource($this->output) || !is_resource($this->errors)) {
      throw new \RuntimeException('Independent queue writer was not started.');
    }
    $output = (string) stream_get_contents($this->output);
    $error = (string) stream_get_contents($this->errors);
    fclose($this->output);
    fclose($this->errors);

    return ['status' => proc_close($this->process), 'output' => $output, 'error' => $error];
  }
}
