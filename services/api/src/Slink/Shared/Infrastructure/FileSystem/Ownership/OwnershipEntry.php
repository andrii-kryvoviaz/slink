<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\FileSystem\Ownership;

final readonly class OwnershipEntry {
  public function __construct(
    private string $path,
    private ?string $owner = null,
    private ?string $group = null,
    private ?int $mode = null,
    private bool $recursive = false,
    private bool $optional = false,
    private bool $glob = false,
  ) {
  }

  public function getPath(): string {
    return $this->path;
  }

  public function getOwner(): ?string {
    return $this->owner;
  }

  public function getGroup(): ?string {
    return $this->group;
  }

  public function getMode(): ?int {
    return $this->mode;
  }

  public function isRecursive(): bool {
    return $this->recursive;
  }

  public function isOptional(): bool {
    return $this->optional;
  }

  public function isGlob(): bool {
    return $this->glob;
  }
}
