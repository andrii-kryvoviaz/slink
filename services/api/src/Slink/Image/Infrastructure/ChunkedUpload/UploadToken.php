<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ChunkedUpload;

use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;

final readonly class UploadToken extends AbstractCompoundValueObject {
  /**
   * @param string $uploadId
   * @param string|null $ownerId
   * @param bool $isGuest
   * @param string $fileName
   * @param int $totalSize
   * @param string $mimeType
   * @param bool $isPublic
   * @param string $description
   * @param array<string> $tagIds
   * @param array<string> $collectionIds
   * @param int $totalChunks
   * @param int $expiresAt
   */
  private function __construct(
    private string $uploadId,
    private ?string $ownerId,
    private bool $isGuest,
    private string $fileName,
    private int $totalSize,
    private string $mimeType,
    private bool $isPublic,
    private string $description,
    private array $tagIds,
    private array $collectionIds,
    private int $totalChunks,
    private int $expiresAt,
  ) {
  }

  /**
   * @param string $uploadId
   * @param string|null $ownerId
   * @param bool $isGuest
   * @param string $fileName
   * @param int $totalSize
   * @param string $mimeType
   * @param bool $isPublic
   * @param string $description
   * @param array<string> $tagIds
   * @param array<string> $collectionIds
   * @param int $totalChunks
   * @param int $expiresAt
   * @return self
   */
  public static function create(
    string $uploadId,
    ?string $ownerId,
    bool $isGuest,
    string $fileName,
    int $totalSize,
    string $mimeType,
    bool $isPublic,
    string $description,
    array $tagIds,
    array $collectionIds,
    int $totalChunks,
    int $expiresAt,
  ): self {
    return new self(
      $uploadId,
      $ownerId,
      $isGuest,
      $fileName,
      $totalSize,
      $mimeType,
      $isPublic,
      $description,
      $tagIds,
      $collectionIds,
      $totalChunks,
      $expiresAt,
    );
  }

  /**
   * @return string
   */
  public function getUploadId(): string {
    return $this->uploadId;
  }

  /**
   * @return string|null
   */
  public function getOwnerId(): ?string {
    return $this->ownerId;
  }

  /**
   * @return bool
   */
  public function isGuest(): bool {
    return $this->isGuest;
  }

  /**
   * @return string
   */
  public function getFileName(): string {
    return $this->fileName;
  }

  /**
   * @return int
   */
  public function getTotalSize(): int {
    return $this->totalSize;
  }

  /**
   * @return string
   */
  public function getMimeType(): string {
    return $this->mimeType;
  }

  /**
   * @return bool
   */
  public function isPublic(): bool {
    return $this->isPublic;
  }

  /**
   * @return string
   */
  public function getDescription(): string {
    return $this->description;
  }

  /**
   * @return array<string>
   */
  public function getTagIds(): array {
    return $this->tagIds;
  }

  /**
   * @return array<string>
   */
  public function getCollectionIds(): array {
    return $this->collectionIds;
  }

  /**
   * @return int
   */
  public function getTotalChunks(): int {
    return $this->totalChunks;
  }

  /**
   * @return int
   */
  public function getExpiresAt(): int {
    return $this->expiresAt;
  }

  public function isExpired(int $now): bool {
    return $this->expiresAt <= $now;
  }

  public function toPayload(): array {
    return [
      'uploadId' => $this->uploadId,
      'ownerId' => $this->ownerId,
      'isGuest' => $this->isGuest,
      'fileName' => $this->fileName,
      'totalSize' => $this->totalSize,
      'mimeType' => $this->mimeType,
      'isPublic' => $this->isPublic,
      'description' => $this->description,
      'tagIds' => $this->tagIds,
      'collectionIds' => $this->collectionIds,
      'totalChunks' => $this->totalChunks,
      'expiresAt' => $this->expiresAt,
    ];
  }

  public static function fromPayload(array $payload): static {
    /** @var array<string> $tagIds */
    $tagIds = $payload['tagIds'] ?? [];

    /** @var array<string> $collectionIds */
    $collectionIds = $payload['collectionIds'] ?? [];

    return new self(
      (string) $payload['uploadId'],
      $payload['ownerId'] !== null ? (string) $payload['ownerId'] : null,
      (bool) $payload['isGuest'],
      (string) $payload['fileName'],
      (int) $payload['totalSize'],
      (string) $payload['mimeType'],
      (bool) $payload['isPublic'],
      (string) $payload['description'],
      $tagIds,
      $collectionIds,
      (int) $payload['totalChunks'],
      (int) $payload['expiresAt'],
    );
  }
}
