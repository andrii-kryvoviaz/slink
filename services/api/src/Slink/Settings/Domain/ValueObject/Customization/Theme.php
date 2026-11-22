<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Customization;

use Slink\Settings\Domain\Enum\ColorMode;
use Slink\Settings\Domain\Exception\InvalidThemeException;
use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;
use Slink\User\Domain\Enum\DisplayTheme;

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
  private const int MAX_CSS_LENGTH = 65536;

  /**
   * @param array<string, string>|null $light null only for built-in themes, which keep their stock colors
   * @param array<string, string>|null $dark null only for built-in themes, which keep their stock colors
   */
  private function __construct(
    private string $id,
    private string $name,
    private ?array $light,
    private ?array $dark,
    private ?float $radius,
    private ColorMode $colorMode,
    private string $css,
  ) {
    if (mb_strlen($id) > self::MAX_ID_LENGTH || !preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/D', $id)) {
      throw new InvalidThemeException('Theme id must be a lowercase slug of up to 48 characters');
    }

    if ($name === '' || mb_strlen($name) > self::MAX_NAME_LENGTH) {
      throw new InvalidThemeException('Theme name must be between 1 and 48 characters');
    }

    if (($light === null) !== ($dark === null)) {
      throw new InvalidThemeException('Theme light and dark palettes must either both be set or both be null');
    }

    if ($light === null && !in_array($id, DisplayTheme::values(), true)) {
      throw new InvalidThemeException('Only built-in themes can use stock colors');
    }

    if ($radius !== null && ($radius < 0 || $radius > self::MAX_RADIUS)) {
      throw new InvalidThemeException('Theme radius must be between 0 and 2');
    }

    if (mb_strlen($css) > self::MAX_CSS_LENGTH) {
      throw new InvalidThemeException('Theme CSS cannot be longer than 65536 characters');
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
      'colorMode' => $this->colorMode->value,
      'css' => $this->css,
    ];
  }

  #[\Override]
  public static function fromPayload(array $payload): static {
    $radius = $payload['radius'] ?? null;

    if ($radius !== null && !is_int($radius) && !is_float($radius)) {
      throw new InvalidThemeException('Theme radius must be between 0 and 2');
    }

    $light = $payload['light'] ?? null;
    $dark = $payload['dark'] ?? null;

    return new self(
      self::text($payload['id'] ?? null),
      trim(self::text($payload['name'] ?? null)),
      $light === null ? null : self::palette($light),
      $dark === null ? null : self::palette($dark),
      $radius === null ? null : (float) $radius,
      self::colorMode($payload['colorMode'] ?? null),
      self::css($payload['css'] ?? null),
    );
  }

  public function getId(): string {
    return $this->id;
  }

  private static function text(mixed $value): string {
    return is_string($value) ? $value : '';
  }

  private static function colorMode(mixed $colorMode): ColorMode {
    if ($colorMode === null) {
      return ColorMode::Both;
    }

    $mode = is_string($colorMode) ? ColorMode::tryFrom($colorMode) : null;

    if ($mode === null) {
      throw new InvalidThemeException('Theme color mode must be one of both, light or dark');
    }

    return $mode;
  }

  private static function css(mixed $css): string {
    if ($css === null) {
      return '';
    }

    if (!is_string($css)) {
      throw new InvalidThemeException('Theme CSS must be a string');
    }

    return $css;
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
