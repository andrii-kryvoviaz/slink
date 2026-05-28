<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Http;

final readonly class CachePolicy {
  private function __construct(
    public bool $shared,
    public bool $immutable,
    public int $maxAge,
    public bool $noCache,
    public bool $mustRevalidate,
  ) {}

  public static function revocable(): self {
    return new self(
      shared: false,
      immutable: false,
      maxAge: 0,
      noCache: true,
      mustRevalidate: true,
    );
  }

  public static function publicImmutable(): self {
    return new self(
      shared: true,
      immutable: true,
      maxAge: 31536000,
      noCache: false,
      mustRevalidate: false,
    );
  }

  public static function privateImmutable(): self {
    return new self(
      shared: false,
      immutable: true,
      maxAge: 31536000,
      noCache: false,
      mustRevalidate: false,
    );
  }

  public static function forImageAccess(bool $isOwned): self {
    if ($isOwned) {
      return self::privateImmutable();
    }

    return self::revocable();
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'public' => $this->shared,
      'private' => !$this->shared,
      'immutable' => $this->immutable,
      'max_age' => $this->maxAge,
      'no_cache' => $this->noCache,
      'must_revalidate' => $this->mustRevalidate,
    ];
  }
}
