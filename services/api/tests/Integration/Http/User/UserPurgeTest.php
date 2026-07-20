<?php

declare(strict_types=1);

namespace Tests\Integration\Http\User;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class UserPurgeTest extends HttpTestCase {
  private string $adminId = '';
  private string $adminToken = '';

  private function bootAdmin(): void {
    $this->adminId = $this->createUser('admin@local.test', 'adminuser', self::PASSWORD);
    $this->grantAdmin($this->adminId);
    $this->adminToken = $this->login('adminuser', self::PASSWORD);
  }

  private function purge(?string $token, string $userId): int {
    return $this->apiRequest('DELETE', '/api/user/' . $userId, $token);
  }

  private function postComment(string $token, string $imageId, string $content): void {
    $status = $this->apiRequest(
      'POST',
      \sprintf('/api/image/%s/comments', $imageId),
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['content' => $content], JSON_THROW_ON_ERROR),
    );

    self::assertSame(201, $status, 'Post comment failed: ' . (string) $this->client->getResponse()->getContent());
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  private function fetchComments(string $imageId, string $token): array {
    $status = $this->apiRequest('GET', \sprintf('/api/image/%s/comments', $imageId), $token);
    self::assertSame(200, $status, 'Fetch comments failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{data: array<int, array<string, mixed>>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload['data'];
  }

  private function publishImageInOwnCollection(string $token, string $imageId): string {
    $collectionId = $this->createCollection($token);
    $this->addImageToCollection($token, $collectionId, $imageId);
    $shareId = $this->createCollectionShare($token, $collectionId);
    $this->publishShare($token, $shareId);

    return $collectionId;
  }

  private function createTag(string $token, string $name): void {
    $status = $this->apiRequest(
      'POST',
      '/api/tags',
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['name' => $name], JSON_THROW_ON_ERROR),
    );

    self::assertContains($status, [200, 201], 'Create tag failed: ' . (string) $this->client->getResponse()->getContent());
  }

  /**
   * @return array<int, string>
   */
  private function listBookmarkedImageIds(string $token): array {
    $status = $this->apiRequest('GET', '/api/bookmarks', $token);
    self::assertSame(200, $status, 'List bookmarks failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{data?: array<int, array{image?: array{id?: string}}>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    $ids = [];
    foreach ($payload['data'] ?? [] as $item) {
      $imageId = $item['image']['id'] ?? null;
      if (\is_string($imageId)) {
        $ids[] = $imageId;
      }
    }

    return $ids;
  }

  /**
   * @return array<int, string>
   */
  private function listUserIds(string $adminToken): array {
    $status = $this->apiRequest('GET', '/api/users/1', $adminToken);
    self::assertSame(200, $status, 'List users failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{data?: array<int, array{id?: string}>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    $ids = [];
    foreach ($payload['data'] ?? [] as $item) {
      if (isset($item['id'])) {
        $ids[] = $item['id'];
      }
    }

    return $ids;
  }

  private function countRowsByUserId(string $table, string $userId): int {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return (int) $entityManager->getConnection()->fetchOne(
      \sprintf('SELECT COUNT(*) FROM "%s" WHERE user_id = :userId', $table),
      ['userId' => $userId],
    );
  }

  private function allowRegistration(): void {
    $this->saveSettings('user', [
      'approvalRequired' => false,
      'allowRegistration' => true,
      'password' => [
        'minLength' => 8,
        'requirements' => 0,
      ],
    ]);
  }

  private function signUp(string $email, string $username): int {
    return $this->apiRequest(
      'POST',
      '/api/auth/signup',
      null,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode([
        'email' => $email,
        'username' => $username,
        'password' => self::PASSWORD,
        'confirm' => self::PASSWORD,
      ], JSON_THROW_ON_ERROR),
    );
  }

  #[Test]
  public function anonymousCannotPurge(): void {
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);

    self::assertSame(401, $this->purge(null, $memberId));
  }

  #[Test]
  public function nonAdminCannotPurge(): void {
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $this->createUser('nonowner@local.test', 'nonowneruser', self::PASSWORD);
    $nonOwnerToken = $this->login('nonowneruser', self::PASSWORD);

    self::assertSame(403, $this->purge($nonOwnerToken, $memberId));
  }

  #[Test]
  public function adminPurgeOfUnknownUserReturnsNotFound(): void {
    $this->bootAdmin();

    self::assertSame(404, $this->purge($this->adminToken, '11111111-2222-3333-4444-555555555555'));
  }

  #[Test]
  public function adminCannotPurgeOwnAccount(): void {
    $this->bootAdmin();

    self::assertSame(400, $this->purge($this->adminToken, $this->adminId));
  }

  #[Test]
  public function nonUuidIdDoesNotMatchPurgeRoute(): void {
    $this->bootAdmin();

    self::assertSame(422, $this->purge($this->adminToken, 'role'));
  }

  #[Test]
  public function purgeCascadesAcrossOwnedContentAndIsIdempotent(): void {
    $this->setAccessSettings([]);
    $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $this->createUser('nonowner@local.test', 'nonowneruser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);
    $nonOwnerToken = $this->login('nonowneruser', self::PASSWORD);

    $memberImage = $this->uploadImage($memberToken, true);
    $imageShare = $this->createImageShare($memberToken, $memberImage);
    $this->publishShare($memberToken, $imageShare);

    $otherImage = $this->uploadImage($nonOwnerToken, true);

    $this->createTag($memberToken, 'member-tag');

    self::assertContains($this->apiRequest('POST', \sprintf('/api/image/%s/bookmark', $memberImage), $nonOwnerToken), [200, 201]);
    self::assertContains($this->apiRequest('POST', \sprintf('/api/image/%s/bookmark', $otherImage), $memberToken), [200, 201]);

    $this->postComment($memberToken, $otherImage, 'member comment on other image');
    $this->postComment($nonOwnerToken, $memberImage, 'nonowner comment on member image');

    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/image/%s.png', $memberImage)));
    self::assertSame(1, $this->countRowsByUserId('bookmark', $memberId));
    self::assertSame(1, $this->countRowsByUserId('tag', $memberId));
    self::assertGreaterThan(0, $this->countRowsByUserId('notification', $memberId));

    $purgeStatus = $this->purge($this->adminToken, $memberId);
    self::assertSame(204, $purgeStatus, 'Purge failed: ' . (string) $this->client->getResponse()->getContent());

    self::assertSame(404, $this->apiRequest('GET', \sprintf('/api/image/%s.png', $memberImage)));
    self::assertSame(0, $this->countRowsByUserId('bookmark', $memberId));
    self::assertSame(0, $this->countRowsByUserId('tag', $memberId));
    self::assertSame(0, $this->countRowsByUserId('notification', $memberId));
    self::assertNotContains($memberId, $this->listUserIds($this->adminToken));
    self::assertSame(401, $this->apiRequest('GET', '/api/user', $memberToken));

    $comments = $this->fetchComments($otherImage, $nonOwnerToken);
    self::assertCount(1, $comments);
    self::assertSame('member comment on other image', $comments[0]['displayContent'] ?? null);
    self::assertArrayHasKey('author', $comments[0]);
    self::assertNull($comments[0]['author']);

    $this->allowRegistration();
    self::assertContains($this->signUp('member@local.test', 'memberuser'), [200, 201, 204]);

    self::assertSame(204, $this->purge($this->adminToken, $memberId));
  }

  #[Test]
  public function purgeRemovesOtherUsersBookmarksOfPurgedImages(): void {
    $this->setAccessSettings([]);
    $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $this->createUser('nonowner@local.test', 'nonowneruser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);
    $nonOwnerToken = $this->login('nonowneruser', self::PASSWORD);

    $memberImage = $this->uploadImage($memberToken, true);
    self::assertContains($this->apiRequest('POST', \sprintf('/api/image/%s/bookmark', $memberImage), $nonOwnerToken), [200, 201]);
    self::assertContains($memberImage, $this->listBookmarkedImageIds($nonOwnerToken));

    self::assertSame(204, $this->purge($this->adminToken, $memberId));

    self::assertNotContains($memberImage, $this->listBookmarkedImageIds($nonOwnerToken));
  }

  #[Test]
  public function purgeDeletesOwnedCollections(): void {
    $this->setAccessSettings([]);
    $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);

    $memberImage = $this->uploadImage($memberToken, true);
    $memberCollection = $this->publishImageInOwnCollection($memberToken, $memberImage);

    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/collection/%s', $memberCollection)));

    $purgeStatus = $this->purge($this->adminToken, $memberId);
    self::assertSame(204, $purgeStatus, 'Purge failed: ' . (string) $this->client->getResponse()->getContent());

    self::assertSame(404, $this->apiRequest('GET', \sprintf('/api/collection/%s', $memberCollection)));
  }

  #[Test]
  public function purgeOfActiveUserAnonymizesCommentsAndFreesIdentity(): void {
    $this->setAccessSettings([]);
    $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);

    $adminImage = $this->uploadImage($this->adminToken, true);
    $this->publishImageInOwnCollection($this->adminToken, $adminImage);
    $this->postComment($memberToken, $adminImage, 'member comment on admin image');

    self::assertSame(204, $this->purge($this->adminToken, $memberId));

    $comments = $this->fetchComments($adminImage, $this->adminToken);
    self::assertCount(1, $comments);
    self::assertNull($comments[0]['author']);

    $this->allowRegistration();
    self::assertContains($this->signUp('member@local.test', 'memberuser'), [200, 201, 204]);
  }
}
