<?php

declare(strict_types=1);

namespace Tests\Integration\Http\ReadModel;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bridge\Doctrine\Middleware\Debug\DebugDataHolder;
use Tests\Integration\Http\HttpTestCase;

final class ChildViewLoadWritesTest extends HttpTestCase {
  private string $ownerToken = '';

  private function entityManager(): EntityManagerInterface {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return $entityManager;
  }

  /**
   * @return array<int, string>
   */
  private function updatesIssuedTo(string $table, callable $action): array {
    /** @var DebugDataHolder $holder */
    $holder = static::getContainer()->get('doctrine.debug_data_holder');

    $this->entityManager()->clear();
    $holder->reset();

    $action();

    /** @var array<int, array{sql: string}> $queries */
    $queries = $holder->getData()['read_model'] ?? [];
    $statements = \array_column($queries, 'sql');

    return \array_values(\array_filter(
      $statements,
      static fn(string $sql): bool => \preg_match('/^\s*UPDATE\s+["`]?' . $table . '["`]?\s/i', $sql) === 1,
    ));
  }

  #[Test]
  public function addingToACollectionDoesNotRewriteItsExistingItems(): void {
    $this->createUser('child-view-owner@local.test', 'childviewowner', self::PASSWORD);
    $this->ownerToken = $this->login('childviewowner', self::PASSWORD);
    $collectionId = $this->createCollection($this->ownerToken);

    foreach (\range(1, 4) as $ignored) {
      $this->addImageToCollection($this->ownerToken, $collectionId, $this->uploadImage($this->ownerToken, false));
    }

    $newImageId = $this->uploadImage($this->ownerToken, false);

    $updates = $this->updatesIssuedTo('collection_item', function () use ($collectionId, $newImageId): void {
      $status = $this->apiRequest(
        'POST',
        \sprintf('/api/collection/%s/items/%s', $collectionId, $newImageId),
        $this->ownerToken,
      );
      self::assertContains($status, [200, 201, 204], (string) $this->client->getResponse()->getContent());
    });

    self::assertSame([], $updates, 'Existing collection items must not be rewritten when one is added.');
  }

  #[Test]
  public function signingInDoesNotRewriteTheUsersExistingRefreshTokens(): void {
    $this->createUser('child-view-owner@local.test', 'childviewowner', self::PASSWORD);

    foreach (\range(1, 3) as $ignored) {
      $this->login('childviewowner', self::PASSWORD);
    }

    $updates = $this->updatesIssuedTo('refresh_token', function (): void {
      $this->login('childviewowner', self::PASSWORD);
    });

    self::assertSame([], $updates, 'Existing refresh tokens must not be rewritten on sign-in.');
  }

  #[Test]
  public function changingTheProfileDoesNotRewriteTheUsersRefreshTokens(): void {
    $this->createUser('child-view-owner@local.test', 'childviewowner', self::PASSWORD);

    foreach (\range(1, 2) as $ignored) {
      $this->login('childviewowner', self::PASSWORD);
    }

    $token = $this->login('childviewowner', self::PASSWORD);

    $updates = $this->updatesIssuedTo('refresh_token', function () use ($token): void {
      $status = $this->updateDisplayName($token, 'Renamed Owner');
      self::assertContains($status, [200, 204], (string) $this->client->getResponse()->getContent());
    });

    self::assertSame([], $updates, 'Existing refresh tokens must not be rewritten on a profile change.');
  }
}
