<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Concurrency;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

final class ConcurrentImageTagTest extends ConcurrentRequestTestCase {
  private string $ownerToken = '';
  private string $attachedTagId = '';
  private string $freeTagId = '';
  private string $winnerTagId = '';

  protected function setUp(): void {
    parent::setUp();

    $this->createUser('tag-race-owner@local.test', 'tagraceowner', self::PASSWORD);
    $this->ownerToken = $this->login('tagraceowner', self::PASSWORD);
    $this->attachedTagId = $this->createTag('attached');
    $this->freeTagId = $this->createTag('free');
    $this->winnerTagId = $this->createTag('winner');
  }

  /**
   * @return iterable<string, array{string}>
   */
  public static function losingTagWrites(): iterable {
    yield 'tag' => ['tag'];
    yield 'untag' => ['untag'];
    yield 'batch reassign' => ['reassign'];
  }

  private function createTag(string $name): string {
    $status = $this->sendJson('POST', '/api/tags', $this->ownerToken, ['name' => $name]);

    self::assertSame(201, $status, (string) $this->client->getResponse()->getContent());

    return $this->extractId((string) $this->client->getResponse()->getContent());
  }

  private function tagPath(string $imageId, string $tagId): string {
    return \sprintf('/api/images/%s/tags/%s', $imageId, $tagId);
  }

  private function performLosingWrite(string $write, string $imageId): int {
    return match ($write) {
      'tag' => $this->apiRequest('POST', $this->tagPath($imageId, $this->freeTagId), $this->ownerToken),
      'untag' => $this->apiRequest('DELETE', $this->tagPath($imageId, $this->attachedTagId), $this->ownerToken),
      'reassign' => $this->sendJson('PUT', '/api/images/batch', $this->ownerToken, [
        'assignments' => [$imageId => ['tagIds' => [$this->freeTagId]]],
      ]),
      default => self::fail('Unknown write ' . $write),
    };
  }

  /**
   * @return list<string>
   */
  private function tagNamesOf(string $imageId): array {
    /** @var list<string> $names */
    $names = $this->connection()->fetchFirstColumn(
      'SELECT t.name FROM image_to_tag it JOIN tag t ON t.uuid = it.tag_id WHERE it.image_id = ? ORDER BY t.name',
      [$imageId],
    );

    return $names;
  }

  #[Test]
  #[DataProvider('losingTagWrites')]
  public function aTagWriteOverlappingAnotherTagWriteOnTheSameImageAnswersConflictAndKeepsTheWinnersTags(
    string $write,
  ): void {
    $imageId = $this->uploadImage($this->ownerToken, false);
    self::assertSame(204, $this->apiRequest('POST', $this->tagPath($imageId, $this->attachedTagId), $this->ownerToken));
    $winner = null;

    $this->beforeTheNextFlush(function () use (&$winner, $imageId): void {
      $winner = $this->requestFromAnotherKernel('POST', $this->tagPath($imageId, $this->winnerTagId), $this->ownerToken);
    });

    $status = $this->performLosingWrite($write, $imageId);

    self::assertSame(204, $winner['status'] ?? null, (string) ($winner['body'] ?? ''));
    self::assertSame(409, $status, (string) $this->client->getResponse()->getContent());
    self::assertSame(['attached', 'winner'], $this->tagNamesOf($imageId));
  }
}
