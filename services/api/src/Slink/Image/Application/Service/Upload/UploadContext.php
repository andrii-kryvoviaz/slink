<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Upload;

use Slink\Image\Application\Command\UploadImage\UploadImageCommand;
use Slink\Image\Domain\Enum\License;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageFile;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\ValueObject\UserPreferences;
use Symfony\Component\HttpFoundation\File\File;

final class UploadContext {
  private ?UserPreferences $preferences = null;

  private ?string $fileName = null;

  private ?ImageFile $mediaFile = null;

  private ?ImageMetadata $metadata = null;

  private bool $isPublic = false;

  private ?License $license = null;

  /**
   * @param array<string> $tagIds
   * @param array<string> $collectionIds
   */
  private function __construct(
    private readonly ID $id,
    private readonly ?ID $userId,
    private readonly bool $requestedPublic,
    private readonly ?string $description,
    private readonly array $tagIds,
    private readonly array $collectionIds,
    private File $file,
  ) {
  }

  public static function fromCommand(UploadImageCommand $command, ?string $userId): self {
    return new self(
      id: $command->getId(),
      userId: $userId !== null ? ID::fromString($userId) : null,
      requestedPublic: $command->isPublic(),
      description: $command->getDescription(),
      tagIds: $command->getTagIds(),
      collectionIds: $command->getCollectionIds(),
      file: $command->getImageFile(),
    );
  }

  public function withPreferences(UserPreferences $preferences): self {
    $clone = clone $this;
    $clone->preferences = $preferences;

    return $clone;
  }

  public function withFile(File $file): self {
    $clone = clone $this;
    $clone->file = $file;

    return $clone;
  }

  public function withDescribed(string $fileName, ImageFile $mediaFile, ImageMetadata $metadata): self {
    $clone = clone $this;
    $clone->fileName = $fileName;
    $clone->mediaFile = $mediaFile;
    $clone->metadata = $metadata;

    return $clone;
  }

  public function withVisibility(bool $isPublic): self {
    $clone = clone $this;
    $clone->isPublic = $isPublic;

    return $clone;
  }

  public function withLicense(?License $license): self {
    $clone = clone $this;
    $clone->license = $license;

    return $clone;
  }

  public function id(): ID {
    return $this->id;
  }

  public function userId(): ?ID {
    return $this->userId;
  }

  public function requestedPublic(): bool {
    return $this->requestedPublic;
  }

  public function description(): ?string {
    return $this->description;
  }

  /**
   * @return array<string>
   */
  public function tagIds(): array {
    return $this->tagIds;
  }

  /**
   * @return array<string>
   */
  public function collectionIds(): array {
    return $this->collectionIds;
  }

  public function file(): File {
    return $this->file;
  }

  public function preferences(): UserPreferences {
    return $this->preferences ?? throw new \LogicException('preferences not loaded');
  }

  public function fileName(): string {
    return $this->fileName ?? throw new \LogicException('fileName not resolved');
  }

  public function mediaFile(): ImageFile {
    return $this->mediaFile ?? throw new \LogicException('mediaFile not resolved');
  }

  public function metadata(): ImageMetadata {
    return $this->metadata ?? throw new \LogicException('metadata not resolved');
  }

  public function isPublic(): bool {
    return $this->isPublic;
  }

  public function license(): ?License {
    return $this->license;
  }

  /**
   * @throws DateTimeException
   */
  public function attributes(): ImageAttributes {
    return ImageAttributes::create($this->fileName(), $this->description ?? '', $this->isPublic);
  }
}
