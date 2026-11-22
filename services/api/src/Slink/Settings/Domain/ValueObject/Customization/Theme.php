<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Customization;

use Slink\Settings\Domain\Exception\InvalidThemeException;
use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;

final readonly class Theme extends AbstractCompoundValueObject {
  public const array COLORS = [
    'background',
    'foreground',
    'primary',
    'accent',
    'success',
    'warning',
    'danger',
    'info',
  ];

  private const int MAX_ID_LENGTH = 48;
  private const int MAX_NAME_LENGTH = 48;
  private const int MAX_RADIUS = 2;

  /**
   * @param array<string, string> $light
   * @param array<string, string> $dark
   */
  private function __construct(
    private string $id,
    private string $name,
    private array $light,
    private array $dark,
    private ?float $radius,
  ) {
    if (mb_strlen($id) > self::MAX_ID_LENGTH || !preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/D', $id)) {
      throw new InvalidThemeException('Theme id must be a lowercase slug of up to 48 characters');
    }

    if ($name === '' || mb_strlen($name) > self::MAX_NAME_LENGTH) {
      throw new InvalidThemeException('Theme name must be between 1 and 48 characters');
    }

    if ($radius !== null && ($radius < 0 || $radius > self::MAX_RADIUS)) {
      throw new InvalidThemeException('Theme radius must be between 0 and 2');
    }
  }

  #[\Override]
  public function toPayload(): array {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'light' => $this->light,
      'dark' => $this->dark,
      'radius' => $this->radius,
    ];
  }

  #[\Override]
  public static function fromPayload(array $payload): static {
    $radius = $payload['radius'] ?? null;

    if ($radius !== null && !is_int($radius) && !is_float($radius)) {
      throw new InvalidThemeException('Theme radius must be between 0 and 2');
    }

    return new self(
      self::text($payload['id'] ?? null),
      trim(self::text($payload['name'] ?? null)),
      self::palette($payload['light'] ?? null),
      self::palette($payload['dark'] ?? null),
      $radius === null ? null : (float) $radius,
    );
  }

  public function getId(): string {
    return $this->id;
  }

  private static function text(mixed $value): string {
    return is_string($value) ? $value : '';
  }

  /**
   * @return array<string, string>
   */
  private static function palette(mixed $palette): array {
    $colors = [];

    foreach (self::COLORS as $name) {
      $color = is_array($palette) ? self::text($palette[$name] ?? null) : '';

      if (!preg_match('/^#[0-9a-f]{6}$/iD', $color)) {
        throw new InvalidThemeException(sprintf('Theme color "%s" must be a hex color like #1a2b3c', $name));
      }

      $colors[$name] = strtolower($color);
    }

    return $colors;
  }
}
