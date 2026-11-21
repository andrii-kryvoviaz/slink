<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Comment;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Comment\Domain\Comment;
use Slink\Comment\Domain\Repository\CommentStoreRepositoryInterface;
use Slink\Comment\Domain\ValueObject\CommentContent;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tests\Integration\Http\Store\ViewBackedStoreRoundTripTestCase;

/**
 * @extends ViewBackedStoreRoundTripTestCase<Comment>
 */
#[Group('sc-1176')]
final class CommentStoreRoundTripTest extends ViewBackedStoreRoundTripTestCase {
  private const string TIMESTAMP_FORMAT = 'Y-m-d H:i:s';
  private const string STORED_TIMESTAMP = '2021-02-03 04:05:06';
  private const string UNKNOWN_COMMENT_ID = '11111111-2222-4333-8444-555555555555';
  private const int LONGEST_ESCAPED_AMPERSANDS = 400;

  private string $adminToken = '';
  private string $authorId = '';
  private string $authorToken = '';
  private string $imageId = '';

  /**
   * @return iterable<string, array{string}>
   */
  public static function escapableTexts(): iterable {
    yield 'ampersand' => ['Tom & Jerry'];
    yield 'angle brackets' => ['1 < 2 > 0'];
    yield 'double quotes' => ['say "hello"'];
    yield 'single quotes' => ["it's fine"];
    yield 'everything at once' => ['<b>"Tom" & \'Jerry\'</b>'];
  }

  #[\Override]
  protected function createAggregate(): ID {
    $this->bootParties();
    $parentId = $this->postComment($this->authorToken, $this->imageId, 'parent comment');

    return ID::fromString($this->postComment($this->authorToken, $this->imageId, 'a reply & more', $parentId));
  }

  #[\Override]
  protected function fetch(ID $id): Comment {
    return $this->storeOf(static::getContainer())->get($id);
  }

  #[\Override]
  protected function aggregateState(object $aggregate): array {
    return [
      'imageId' => $aggregate->getImageId()->toString(),
      'authorId' => $aggregate->getUserId()->toString(),
      'referencedCommentId' => $aggregate->getReferencedCommentId()?->toString(),
      'content' => $aggregate->getContent()->toString(),
      'createdAt' => $aggregate->getCreatedAt()->format(self::TIMESTAMP_FORMAT),
      'updatedAt' => $aggregate->getUpdatedAt()?->format(self::TIMESTAMP_FORMAT),
      'deletedAt' => $aggregate->getDeletedAt()?->format(self::TIMESTAMP_FORMAT),
    ];
  }

  #[\Override]
  protected function storedState(ID $id): array {
    $row = $this->readModel()->fetchAssociative('SELECT * FROM "comment" WHERE uuid = ?', [$id->toString()]);
    self::assertIsArray($row);

    return [
      'imageId' => $row['image_id'],
      'authorId' => $row['user_id'],
      'referencedCommentId' => $row['referenced_comment_id'],
      'content' => $row['content'],
      'createdAt' => $this->secondsOf($row['created_at']),
      'updatedAt' => $this->secondsOf($row['updated_at']),
      'deletedAt' => $this->secondsOf($row['deleted_at']),
    ];
  }

  #[\Override]
  protected function rootTable(): string {
    return 'comment';
  }

  #[Test]
  public function postingEditingAndDeletingACommentChangesItsRow(): void {
    $this->bootParties();
    $commentId = $this->postComment($this->authorToken, $this->imageId, 'first words');

    $this->assertEdited($this->edit($commentId, 'second words'));
    self::assertSame('second words', $this->rowContent($commentId));

    $this->assertDeleted($this->delete($commentId, $this->authorToken));
    self::assertNotNull($this->rowColumn($commentId, 'deleted_at'));
  }

  #[Test]
  public function reloadedCommentTakesItsStateFromTheRow(): void {
    $id = $this->createAggregate();
    $this->readModel()->executeStatement(
      'UPDATE "comment" SET content = ?, created_at = ?, updated_at = ? WHERE uuid = ?',
      ['rewritten in the row', self::STORED_TIMESTAMP, self::STORED_TIMESTAMP, $id->toString()],
    );

    $comment = $this->reload($id);

    self::assertSame('rewritten in the row', $comment->getContent()->toString());
    self::assertSame(self::STORED_TIMESTAMP, $comment->getCreatedAt()->format(self::TIMESTAMP_FORMAT));
    self::assertSame(self::STORED_TIMESTAMP, $comment->getUpdatedAt()?->format(self::TIMESTAMP_FORMAT));
  }

  #[Test]
  #[DataProvider('escapableTexts')]
  public function aCommentWithEscapableCharactersReloadsIdenticalToItsRow(string $text): void {
    $this->bootParties();
    $commentId = $this->postComment($this->authorToken, $this->imageId, $text);

    $reloaded = $this->reload(ID::fromString($commentId))->getContent()->toString();

    self::assertSame($this->rowContent($commentId), $reloaded);
    self::assertStringNotContainsString('&amp;amp;', $reloaded);
  }

  #[Test]
  public function textThatAlreadyLooksEscapedReloadsIdenticalToItsRow(): void {
    $this->bootParties();
    $commentId = $this->postComment($this->authorToken, $this->imageId, 'AT&amp;T');

    self::assertSame(
      $this->rowContent($commentId),
      $this->reload(ID::fromString($commentId))->getContent()->toString(),
    );
  }

  #[Test]
  public function aCommentOfTheLongestAllowedEscapableTextReloadsIdenticalToItsRow(): void {
    $this->bootParties();
    $commentId = $this->postComment($this->authorToken, $this->imageId, \str_repeat('&', self::LONGEST_ESCAPED_AMPERSANDS));

    self::assertSame(
      $this->rowContent($commentId),
      $this->reload(ID::fromString($commentId))->getContent()->toString(),
    );
  }

  #[Test]
  public function editingACommentKeepsTheStoredContentUnchangedApartFromTheEdit(): void {
    $this->bootParties();
    $commentId = $this->postComment($this->authorToken, $this->imageId, 'Tom & Jerry <b>one</b>');
    $freshlyPosted = $this->postComment($this->authorToken, $this->imageId, 'Tom & Jerry <b>two</b>');

    $this->assertEdited($this->edit($commentId, 'Tom & Jerry <b>two</b>'));

    self::assertSame($this->rowContent($freshlyPosted), $this->rowContent($commentId));
    self::assertSame(
      $this->rowContent($commentId),
      $this->reload(ID::fromString($commentId))->getContent()->toString(),
    );
  }

  #[Test]
  public function editingACommentRepeatedlyNeverEscapesItAgain(): void {
    $this->bootParties();
    $commentId = $this->postComment($this->authorToken, $this->imageId, 'Tom & Jerry');
    $storedContent = $this->rowContent($commentId);

    $this->assertEdited($this->edit($commentId, 'Tom & Jerry again'));
    $this->assertEdited($this->edit($commentId, 'Tom & Jerry'));

    self::assertSame($storedContent, $this->rowContent($commentId));
    self::assertSame($storedContent, $this->reload(ID::fromString($commentId))->getContent()->toString());
  }

  #[Test]
  public function editingACommentToItsOwnTextLeavesTheStoredContentAlone(): void {
    $this->bootParties();
    $commentId = $this->postComment($this->authorToken, $this->imageId, 'Tom & "Jerry"');
    $storedContent = $this->rowContent($commentId);

    $this->assertEdited($this->edit($commentId, 'Tom & "Jerry"'));

    self::assertSame($storedContent, $this->rowContent($commentId));
    self::assertSame($storedContent, $this->reload(ID::fromString($commentId))->getContent()->toString());
  }

  #[Test]
  public function aCommentPastItsEditWindowInTheRowRefusesTheEdit(): void {
    $this->bootParties();
    $commentId = $this->postComment($this->authorToken, $this->imageId, 'too late');
    $this->readModel()->executeStatement(
      'UPDATE "comment" SET created_at = ? WHERE uuid = ?',
      [self::STORED_TIMESTAMP, $commentId],
    );

    self::assertNotContains($this->edit($commentId, 'rewritten'), [200, 204]);

    self::assertSame('too late', $this->rowContent($commentId));
  }

  #[Test]
  public function aCommentBySoftDeletedUserReloadsWithThatUsersId(): void {
    $commentId = $this->createAggregate();

    $this->softDelete($this->adminToken, $this->authorId);

    self::assertSame($this->authorId, $this->reload($commentId)->getUserId()->toString());
  }

  #[Test]
  public function aCommentByPurgedUserReloadsWithThatUsersId(): void {
    $commentId = $this->createAggregate();

    self::assertSame(204, $this->apiRequest('DELETE', '/api/user/' . $this->authorId, $this->adminToken));

    self::assertSame($this->authorId, $this->reload($commentId)->getUserId()->toString());
  }

  #[Test]
  public function theImageOwnerCanStillDeleteACommentByADeletedUser(): void {
    $commentId = $this->createAggregate();
    $this->softDelete($this->adminToken, $this->authorId);

    $this->assertDeleted($this->delete($commentId->toString(), $this->adminToken));

    self::assertNotNull($this->rowColumn($commentId->toString(), 'deleted_at'));
  }

  #[Test]
  public function aReplyToADeletedParentKeepsItsReference(): void {
    $this->bootParties();
    $parentId = $this->postComment($this->authorToken, $this->imageId, 'parent comment');
    $replyId = $this->postComment($this->authorToken, $this->imageId, 'reply', $parentId);

    $this->assertDeleted($this->delete($parentId, $this->authorToken));

    self::assertSame(
      $parentId,
      $this->reload(ID::fromString($replyId))->getReferencedCommentId()?->toString(),
    );
  }

  #[Test]
  public function aSoftDeletedCommentHydratesAndRefusesEditAndDelete(): void {
    $commentId = $this->createAggregate();
    $this->assertDeleted($this->delete($commentId->toString(), $this->authorToken));
    $this->readModel()->executeStatement(
      'UPDATE "comment" SET deleted_at = ? WHERE uuid = ?',
      [self::STORED_TIMESTAMP, $commentId->toString()],
    );

    $comment = $this->reload($commentId);

    self::assertTrue($comment->isDeleted());
    self::assertSame(self::STORED_TIMESTAMP, $comment->getDeletedAt()?->format(self::TIMESTAMP_FORMAT));

    $comment->delete();
    self::assertSame([], $comment->releaseEvents());
    self::assertSame(self::STORED_TIMESTAMP, $comment->getDeletedAt()?->format(self::TIMESTAMP_FORMAT));

    $this->expectException(\DomainException::class);
    $this->expectExceptionMessage('Cannot edit a deleted comment');

    $comment->edit(CommentContent::fromString('should be refused'));
  }

  #[Test]
  public function loadingAnUnknownCommentThrowsNotFound(): void {
    $this->expectException(NotFoundException::class);

    $this->reload(ID::fromString(self::UNKNOWN_COMMENT_ID));
  }

  #[Test]
  public function editingAnUnknownCommentAnswersNotFound(): void {
    $this->bootParties();

    self::assertSame(404, $this->edit(self::UNKNOWN_COMMENT_ID, 'nobody home'));
  }

  #[Test]
  public function deletingAnUnknownCommentAnswersNotFound(): void {
    $this->bootParties();

    self::assertSame(404, $this->delete(self::UNKNOWN_COMMENT_ID, $this->authorToken));
  }

  #[Test]
  public function theSecondOfTwoConcurrentEditorsFailsAndTheFirstEditorsTextStays(): void {
    $commentId = $this->createAggregate();
    $firstContainer = $this->independentContainer();
    $secondContainer = $this->independentContainer();
    $firstLoad = $this->storeOf($firstContainer)->get($commentId);
    $secondLoad = $this->storeOf($secondContainer)->get($commentId);

    $this->editAndStore($firstContainer, $firstLoad, 'first editor');

    self::assertCausedBy(OptimisticLockException::class, $this->failureOf(
      fn () => $this->editAndStore($secondContainer, $secondLoad, 'second editor'),
    ));
    self::assertSame('first editor', $this->rowContent($commentId->toString()));
  }

  private function bootParties(): void {
    $this->adminToken = $this->bootAdmin();
    $this->authorId = $this->createUser('comment-store-author@local.test', 'commentstoreauthor', self::PASSWORD);
    $this->authorToken = $this->login('commentstoreauthor', self::PASSWORD);
    $this->imageId = $this->uploadImage($this->adminToken, true);
  }

  private function edit(string $commentId, string $content): int {
    return $this->apiRequest(
      'PATCH',
      \sprintf('/api/comment/%s', $commentId),
      $this->authorToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['content' => $content], JSON_THROW_ON_ERROR),
    );
  }

  private function delete(string $commentId, string $token): int {
    return $this->apiRequest('DELETE', \sprintf('/api/comment/%s', $commentId), $token);
  }

  private function assertEdited(int $status): void {
    self::assertContains($status, [200, 204], (string) $this->client->getResponse()->getContent());
  }

  private function assertDeleted(int $status): void {
    self::assertContains($status, [200, 202, 204], (string) $this->client->getResponse()->getContent());
  }

  private function editAndStore(ContainerInterface $container, Comment $comment, string $content): void {
    $comment->edit(CommentContent::fromString($content));
    $this->storeOf($container)->store($comment);

    /** @var EntityManagerInterface $entityManager */
    $entityManager = $container->get(EntityManagerInterface::class);
    $entityManager->flush();
  }

  private function storeOf(ContainerInterface $container): CommentStoreRepositoryInterface {
    /** @var CommentStoreRepositoryInterface $store */
    $store = $container->get(CommentStoreRepositoryInterface::class);

    return $store;
  }

  private function rowContent(string $commentId): mixed {
    return $this->rowColumn($commentId, 'content');
  }

  private function rowColumn(string $commentId, string $column): mixed {
    return $this->readModel()->fetchOne(
      \sprintf('SELECT %s FROM "comment" WHERE uuid = ?', $column),
      [$commentId],
    );
  }
}
