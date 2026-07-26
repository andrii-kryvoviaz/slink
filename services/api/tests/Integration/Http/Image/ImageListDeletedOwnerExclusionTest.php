<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Image;

use PHPUnit\Framework\Attributes\Test;
use Slink\User\Domain\Enum\UserStatus;
use Tests\Integration\Http\HttpTestCase;

final class ImageListDeletedOwnerExclusionTest extends HttpTestCase {
  private function uploadGuestImage(): string {
    $this->client->request('POST', '/api/upload', [], ['image' => $this->sampleImage()]);

    $response = $this->client->getResponse();
    self::assertContains(
      $response->getStatusCode(),
      [200, 201],
      'Guest upload failed: ' . (string) $response->getContent(),
    );

    return $this->extractId((string) $response->getContent());
  }

  /**
   * @return array<int, string>
   */
  private function listImageIds(string $token): array {
    $status = $this->apiRequest('GET', '/api/images?limit=50', $token);
    self::assertSame(200, $status, 'Image list failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{data: array<int, array{id: string}>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return \array_map(static fn(array $row): string => $row['id'], $payload['data']);
  }

  private function totalCount(string $token): int {
    $status = $this->apiRequest('GET', '/api/images?limit=50', $token);
    self::assertSame(200, $status, 'Image list failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{meta: array{total: int}} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload['meta']['total'];
  }

  private function exists(string $token): bool {
    $status = $this->apiRequest('GET', '/api/images/exists', $token);
    self::assertSame(200, $status, 'Image exists failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{exists: bool} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload['exists'];
  }

  private function softDelete(string $adminToken, string $userId): void {
    $status = $this->apiRequest(
      'PATCH',
      '/api/user/status',
      $adminToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $userId, 'status' => UserStatus::Deleted->value], JSON_THROW_ON_ERROR),
    );

    self::assertSame(200, $status, 'Soft delete failed: ' . (string) $this->client->getResponse()->getContent());
  }

  #[Test]
  public function listAndCountExcludeDeletedOwnersButKeepOwnerlessImages(): void {
    $this->setAccessSettings(['allowGuestUploads' => true]);

    $adminId = $this->createUser('imglist-admin@local.test', 'imglistadmin', self::PASSWORD);
    $this->grantAdmin($adminId);
    $adminToken = $this->login('imglistadmin', self::PASSWORD);

    $memberId = $this->createUser('imglist-member@local.test', 'imglistmember', self::PASSWORD);
    $memberToken = $this->login('imglistmember', self::PASSWORD);

    $memberImage = $this->uploadImage($memberToken, true);
    $guestImage = $this->uploadGuestImage();

    self::assertEqualsCanonicalizing([$memberImage, $guestImage], $this->listImageIds($adminToken));
    self::assertSame(2, $this->totalCount($adminToken));
    self::assertTrue($this->exists($adminToken));

    $this->softDelete($adminToken, $memberId);

    self::assertSame([$guestImage], $this->listImageIds($adminToken));
    self::assertSame(1, $this->totalCount($adminToken));
    self::assertTrue($this->exists($adminToken));
  }

  #[Test]
  public function existsIsFalseWhenOnlyRemainingImagesBelongToDeletedOwners(): void {
    $this->setAccessSettings([]);

    $adminId = $this->createUser('imglist-admin2@local.test', 'imglistadmin2', self::PASSWORD);
    $this->grantAdmin($adminId);
    $adminToken = $this->login('imglistadmin2', self::PASSWORD);

    $memberId = $this->createUser('imglist-member2@local.test', 'imglistmember2', self::PASSWORD);
    $memberToken = $this->login('imglistmember2', self::PASSWORD);

    $this->uploadImage($memberToken, true);

    self::assertTrue($this->exists($adminToken));

    $this->softDelete($adminToken, $memberId);

    self::assertFalse($this->exists($adminToken));
    self::assertSame(0, $this->totalCount($adminToken));
    self::assertSame([], $this->listImageIds($adminToken));
  }
}
