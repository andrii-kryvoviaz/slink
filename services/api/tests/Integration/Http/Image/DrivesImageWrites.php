<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Image;

use Doctrine\ORM\EntityManagerInterface;

trait DrivesImageWrites {
  private string $ownerId = '';
  private string $ownerToken = '';
  private string $adminToken = '';
  private string $attachedTagId = '';
  private string $freeTagId = '';
  private string $collectionId = '';

  /**
   * @return iterable<string, array{string}>
   */
  public static function singleImageWrites(): iterable {
    yield 'update' => ['update'];
    yield 'admin update' => ['adminUpdate'];
    yield 'tag' => ['tag'];
    yield 'untag' => ['untag'];
    yield 'delete' => ['delete'];
    yield 'admin delete' => ['adminDelete'];
    yield 'add to collection' => ['addToCollection'];
  }

  private function bootActors(): void {
    $this->saveSettings('image', ['maxSize' => '5M', 'enableLicensing' => true]);
    $this->adminToken = $this->bootAdmin();
    $this->ownerId = $this->createUser('image-owner@local.test', 'imageowner', self::PASSWORD);
    $this->ownerToken = $this->login('imageowner', self::PASSWORD);
    $this->attachedTagId = $this->createTag('attached');
    $this->freeTagId = $this->createTag('free');
    $this->collectionId = $this->createCollection($this->ownerToken);
  }

  private function performWrite(string $write, string $imageId): int {
    return match ($write) {
      'update' => $this->updateImage($imageId, ['description' => 'changed', 'isPublic' => true]),
      'adminUpdate' => $this->send('PATCH', \sprintf('/api/admin/image/%s', $imageId), $this->adminToken, ['description' => 'changed', 'isPublic' => true]),
      'tag' => $this->tagImage($imageId, $this->freeTagId),
      'untag' => $this->send('DELETE', $this->imageTagPath($imageId, $this->attachedTagId), $this->ownerToken),
      'delete' => $this->deleteImage($imageId),
      'adminDelete' => $this->send('DELETE', \sprintf('/api/admin/image/%s', $imageId), $this->adminToken, []),
      'addToCollection' => $this->send('POST', \sprintf('/api/collection/%s/items/%s', $this->collectionId, $imageId), $this->ownerToken),
      default => self::fail('Unknown write ' . $write),
    };
  }

  private function uploadDescribedImage(string $description): string {
    $this->forgetLoadedRows();
    $status = $this->uploadImageWithFields($this->ownerToken, ['description' => $description]);
    $content = (string) $this->client->getResponse()->getContent();

    self::assertSame(201, $status, 'Upload failed: ' . $content);

    return $this->extractId($content);
  }

  private function uploadTaggedLicensedImage(string $description): string {
    $imageId = $this->uploadDescribedImage($description);

    self::assertWriteSucceeded($this->tagImage($imageId, $this->attachedTagId));
    self::assertWriteSucceeded($this->updateImage($imageId, ['description' => null, 'isPublic' => false, 'license' => 'cc-by']));
    self::assertWriteSucceeded($this->updateImage($imageId, ['description' => null, 'isPublic' => true]));

    return $imageId;
  }

  private function createTag(string $name): string {
    $status = $this->send('POST', '/api/tags', $this->ownerToken, ['name' => $name]);
    $content = (string) $this->client->getResponse()->getContent();

    self::assertSame(201, $status, 'Create tag failed: ' . $content);

    return $this->extractId($content);
  }

  /**
   * @param array<string, mixed> $fields
   */
  private function updateImage(string $imageId, array $fields): int {
    return $this->send('PATCH', \sprintf('/api/image/%s', $imageId), $this->ownerToken, $fields);
  }

  private function tagImage(string $imageId, string $tagId): int {
    return $this->send('POST', $this->imageTagPath($imageId, $tagId), $this->ownerToken);
  }

  private function deleteImage(string $imageId): int {
    return $this->send('DELETE', \sprintf('/api/image/%s', $imageId), $this->ownerToken);
  }

  private function viewImage(string $imageId): int {
    return $this->send('GET', \sprintf('/api/image/public/%s.png', $imageId), $this->adminToken);
  }

  private function imageTagPath(string $imageId, string $tagId): string {
    return \sprintf('/api/images/%s/tags/%s', $imageId, $tagId);
  }

  private function detailDescription(string $imageId): mixed {
    self::assertSame(200, $this->send('GET', \sprintf('/api/image/%s/detail', $imageId), $this->ownerToken));

    /** @var array{data?: array<string, mixed>} $payload */
    $payload = $this->responsePayload();

    return ($payload['data'] ?? $payload)['description'] ?? null;
  }

  /**
   * @param array<mixed>|null $body
   */
  private function send(string $method, string $path, string $token, ?array $body = null): int {
    $this->forgetLoadedRows();

    if ($body === null) {
      return $this->apiRequest($method, $path, $token);
    }

    return $this->apiRequest(
      $method,
      $path,
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode($body, JSON_THROW_ON_ERROR),
    );
  }

  private function imageColumn(string $imageId, string $column): mixed {
    return $this->doctrine()->getConnection()->fetchOne(
      \sprintf('SELECT %s FROM "image" WHERE uuid = ?', $column),
      [$imageId],
    );
  }

  private function forgetLoadedRows(): void {
    $this->doctrine()->clear();
  }

  private function doctrine(): EntityManagerInterface {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return $entityManager;
  }

  private static function assertWriteSucceeded(int $status): void {
    self::assertContains($status, [200, 201, 204]);
  }
}
