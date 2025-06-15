<?php

declare(strict_types=1);

namespace Slink\Image\Domain\ValueObject;

use InvalidArgumentException;
use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;

final readonly class AnimatedImageInfo extends AbstractCompoundValueObject {
  public function __construct(
    private bool $isAnimated,
    private int  $frameCount = 1
  ) {
    if ($frameCount < 1) {
      throw new InvalidArgumentException('Frame count must be at least 1');
    }
  }

  public static function animated(int $frameCount): self {
    return new self(true, $frameCount);
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      isAnimated: (bool)($payload['isAnimated'] ?? false),
      frameCount: (int)($payload['frameCount'] ?? 1)
    );
  }

  public static function static(): self {
    return new self(false, 1);
  }

  public function getFrameCount(): int {
    return $this->frameCount;
  }

  public function isAnimated(): bool {
    return $this->isAnimated;
  }

  public function isMultiFrame(): bool {
    return $this->frameCount > 1;
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'isAnimated' => $this->isAnimated,
      'frameCount' => $this->frameCount,
    ];
  }
}
