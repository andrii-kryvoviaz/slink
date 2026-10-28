<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Tag;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;
use Tests\Integration\Http\HttpTestCase;

final class MoveTagOwnerScopeTest extends HttpTestCase {
  private const string UNTOUCHED_AT = '2020-01-01 00:00:00';

  private string $ownerId = '';
  private string $otherOwnerId = '';
  private string $ownerToken = '';
  private string $otherOwnerToken = '';

  private function bootActors(): void {
    $this->ownerId = $this->createUser('tag-owner@local.test', 'tagowner', self::PASSWORD);
    $this->otherOwnerId = $this->createUser('tag-other-owner@local.test', 'tagotherowner', self::PASSWORD);

    $this->ownerToken = $this->login('tagowner', self::PASSWORD);
    $this->otherOwnerToken = $this->login('tagotherowner', self::PASSWORD);
  }

  private function createTag(string $token, string $name, ?string $parentId = null): string {
    $status = $this->apiRequest(
      'POST',
      '/api/tags',
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['name' => $name, 'parentId' => $parentId], JSON_THROW_ON_ERROR),
    );

    self::assertSame(201, $status, 'Create tag failed: ' . (string) $this->client->getResponse()->getContent());

    return $this->extractId((string) $this->client->getResponse()->getContent());
  }

  private function moveTag(string $token, string $tagId, ?string $newParentId): int {
    return $this->apiRequest(
      'PATCH',
      '/api/tags/move',
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $tagId, 'newParentId' => $newParentId], JSON_THROW_ON_ERROR),
    );
  }

  private function pathOf(string $tagId): string {
    return $this->freshTag($tagId)->getPath();
  }

  private function updatedAtOf(string $tagId): ?string {
    return $this->freshTag($tagId)->getUpdatedAt()?->format('Y-m-d H:i:s');
  }

  private function freshTag(string $tagId): TagView {
    $this->entityManager()->clear();

    /** @var TagRepositoryInterface $repository */
    $repository = static::getContainer()->get(TagRepositoryInterface::class);

    return $repository->oneById(ID::fromString($tagId));
  }

  private function markUntouched(string $userId, string $name): void {
    $this->entityManager()->getConnection()->executeStatement(
      'UPDATE "tag" SET updated_at = :updatedAt WHERE user_id = :userId AND name = :name',
      ['updatedAt' => self::UNTOUCHED_AT, 'userId' => $userId, 'name' => $name],
    );
  }

  private function entityManager(): EntityManagerInterface {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return $entityManager;
  }

  #[Test]
  #[Group('sc-1068')]
  public function movingOwnTagRewritesOnlyOwnersChildPath(): void {
    $this->bootActors();
    $ownerParent = $this->createTag($this->ownerToken, 'parent');
    $ownerChild = $this->createTag($this->ownerToken, 'child', $ownerParent);
    $bucket = $this->createTag($this->ownerToken, 'bucket');
    $otherParent = $this->createTag($this->otherOwnerToken, 'parent');
    $otherChild = $this->createTag($this->otherOwnerToken, 'child', $otherParent);

    self::assertSame(204, $this->moveTag($this->ownerToken, $ownerParent, $bucket));

    self::assertSame('#bucket/parent/child', $this->pathOf($ownerChild));
    self::assertSame('#parent/child', $this->pathOf($otherChild));
  }

  #[Test]
  #[Group('sc-1068')]
  public function movingOwnTagLeavesOtherOwnersChildUpdatedAtUntouched(): void {
    $this->bootActors();
    $ownerParent = $this->createTag($this->ownerToken, 'parent');
    $ownerChild = $this->createTag($this->ownerToken, 'child', $ownerParent);
    $bucket = $this->createTag($this->ownerToken, 'bucket');
    $otherParent = $this->createTag($this->otherOwnerToken, 'parent');
    $otherChild = $this->createTag($this->otherOwnerToken, 'child', $otherParent);
    $this->markUntouched($this->ownerId, 'child');
    $this->markUntouched($this->otherOwnerId, 'child');

    self::assertSame(204, $this->moveTag($this->ownerToken, $ownerParent, $bucket));

    self::assertNotSame(self::UNTOUCHED_AT, $this->updatedAtOf($ownerChild));
    self::assertSame(self::UNTOUCHED_AT, $this->updatedAtOf($otherChild));
  }

  #[Test]
  #[Group('sc-1068')]
  public function movingOwnTagRewritesOnlyOwnersGrandchildPath(): void {
    $this->bootActors();
    $ownerParent = $this->createTag($this->ownerToken, 'parent');
    $ownerChild = $this->createTag($this->ownerToken, 'child', $ownerParent);
    $ownerLeaf = $this->createTag($this->ownerToken, 'leaf', $ownerChild);
    $bucket = $this->createTag($this->ownerToken, 'bucket');
    $otherParent = $this->createTag($this->otherOwnerToken, 'parent');
    $otherChild = $this->createTag($this->otherOwnerToken, 'child', $otherParent);
    $otherLeaf = $this->createTag($this->otherOwnerToken, 'leaf', $otherChild);

    self::assertSame(204, $this->moveTag($this->ownerToken, $ownerParent, $bucket));

    self::assertSame('#bucket/parent/child/leaf', $this->pathOf($ownerLeaf));
    self::assertSame('#parent/child/leaf', $this->pathOf($otherLeaf));
  }

  #[Test]
  #[Group('sc-1068')]
  public function movingOwnTagToRootRewritesOnlyOwnersDescendantPaths(): void {
    $this->bootActors();
    $ownerOuter = $this->createTag($this->ownerToken, 'outer');
    $ownerParent = $this->createTag($this->ownerToken, 'parent', $ownerOuter);
    $ownerChild = $this->createTag($this->ownerToken, 'child', $ownerParent);
    $otherOuter = $this->createTag($this->otherOwnerToken, 'outer');
    $otherParent = $this->createTag($this->otherOwnerToken, 'parent', $otherOuter);
    $otherChild = $this->createTag($this->otherOwnerToken, 'child', $otherParent);

    self::assertSame(204, $this->moveTag($this->ownerToken, $ownerParent, null));

    self::assertSame('#parent/child', $this->pathOf($ownerChild));
    self::assertSame('#outer/parent/child', $this->pathOf($otherChild));
  }
}
