<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\BatchDeleteImages;

final readonly class BatchDeleteImagesResult {
  /**
   * @param array<string> $deleted
   * @param array<array{id: string, reason: string}> $failed
   */
  public function __construct(
    private array $deleted,
    private array $failed,
  ) {}

  /**
   * @return array<string>
   */
  public function deleted(): array {
    return $this->deleted;
  }

  /**
   * @return array<array{id: string, reason: string}>
   */
  public function failed(): array {
    return $this->failed;
  }

  /**
   * @return array{deleted: array<string>, failed: array<array{id: string, reason: string}>}
   */
  public function toArray(): array {
    return [
      'deleted' => $this->deleted,
      'failed' => $this->failed,
    ];
  }
}
