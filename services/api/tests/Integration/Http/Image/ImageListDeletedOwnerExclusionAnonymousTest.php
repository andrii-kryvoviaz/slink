<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Image;

use PHPUnit\Framework\Attributes\Test;
use Slink\User\Domain\Enum\UserStatus;
use Tests\Integration\Http\HttpTestCase;

final class ImageListDeletedOwnerExclusionAnonymousTest extends HttpTestCase {
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
  private function listImageIds(): array {
    $status = $this->apiRequest('GET', '/api/images?limit=50');
    self::assertSame(200, $status, 'Image list failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{data: array<int, array{id: string}>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return \array_map(static fn(array $row): string => $row['id'], $payload['data']);
  }

  private function totalCount(): int {
    $status = $this->apiRequest('GET', '/api/images?limit=50');
    self::assertSame(200, $status, 'Image list failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{meta: array{total: int}} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload['meta']['total'];
  }

  private function exists(): bool {
    $status = $this->apiRequest('GET', '/api/images/exists');
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
  public function anonymousListAndCountExcludeDeletedOwnersButKeepOwnerlessImages(): void {
    $this->setAccessSettings(['allowGuestUploads' => true, 'allowUnauthenticatedAccess' => true]);

    $adminId = $this->createUser('anon-imglist-admin@local.test', 'anonimglistadmin', self::PASSWORD);
    $this->grantAdmin($adminId);
    $adminToken = $this->login('anonimglistadmin', self::PASSWORD);

    $memberId = $this->createUser('anon-imglist-member@local.test', 'anonimglistmember', self::PASSWORD);
    $memberToken = $this->login('anonimglistmember', self::PASSWORD);

    $memberImage = $this->uploadImage($memberToken, true);
    $guestImage = $this->uploadGuestImage();

    self::assertEqualsCanonicalizing([$memberImage, $guestImage], $this->listImageIds());
    self::assertSame(2, $this->totalCount());
    self::assertTrue($this->exists());

    $this->softDelete($adminToken, $memberId);

    self::assertSame([$guestImage], $this->listImageIds());
    self::assertSame(1, $this->totalCount());
    self::assertTrue($this->exists());
  }

  #[Test]
  public function anonymousExistsIsFalseWhenOnlyRemainingImagesBelongToDeletedOwners(): void {
    $this->setAccessSettings(['allowUnauthenticatedAccess' => true]);

    $adminId = $this->createUser('anon-imglist-admin2@local.test', 'anonimglistadmin2', self::PASSWORD);
    $this->grantAdmin($adminId);
    $adminToken = $this->login('anonimglistadmin2', self::PASSWORD);

    $memberId = $this->createUser('anon-imglist-member2@local.test', 'anonimglistmember2', self::PASSWORD);
    $memberToken = $this->login('anonimglistmember2', self::PASSWORD);

    $this->uploadImage($memberToken, true);

    self::assertTrue($this->exists());

    $this->softDelete($adminToken, $memberId);

    self::assertFalse($this->exists());
    self::assertSame(0, $this->totalCount());
    self::assertSame([], $this->listImageIds());
  }
}
