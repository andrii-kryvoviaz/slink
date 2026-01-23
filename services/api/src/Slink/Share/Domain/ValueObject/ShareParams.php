<?php

declare(strict_types=1);

namespace Slink\Share\Domain\ValueObject;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;

final readonly class ShareParams extends AbstractValueObject {
  private function __construct(
    private ShareableReference $shareable,
    private string $targetPath,
  ) {}

  public static function fromShareable(ShareableReference $shareable): self {
    return new self(
      $shareable,
      $shareable->getShareableType()->targetPath($shareable->getShareableId()),
    );
  }

  public static function withTargetPath(ShareableReference $shareable, string $targetPath): self {
    return new self($shareable, $targetPath);
  }

  public function getShareable(): ShareableReference {
    return $this->shareable;
  }

  public function getTargetPath(): string {
    return $this->targetPath;
  }
}
