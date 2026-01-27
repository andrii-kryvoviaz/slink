<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\ValueObject;

use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use JsonException;

final readonly class Cursor {
  /**
   * @param string $timestamp
   * @param string $id
   * @throws InvalidArgumentException
   */
  public function __construct(
    public string $timestamp,
    public string $id
  ) {
    $this->validateTimestamp($timestamp);
    $this->validateId($id);
  }

  /**
   * @param string $encodedCursor
   * @return self|null
   */
  public static function fromEncodedString(string $encodedCursor): ?self {
    try {
      $cursorData = json_decode(base64_decode($encodedCursor), true, flags: JSON_THROW_ON_ERROR);

      if (!is_array($cursorData) || !isset($cursorData['timestamp'], $cursorData['id'])) {
        return null;
      }

      return new self($cursorData['timestamp'], $cursorData['id']);
    } catch (JsonException|InvalidArgumentException) {
      return null;
    }
  }

  /**
   * @param DateTimeInterface $dateTime
   * @param string $id
   * @return self
   * @throws InvalidArgumentException
   */
  public static function fromEntityData(DateTimeInterface $dateTime, string $id): self {
    return new self(
      $dateTime->format('Y-m-d H:i:s.u'),
      $id
    );
  }

  /**
   * @param string $jsonCursor
   * @return self|null
   */
  public static function fromJsonString(string $jsonCursor): ?self {
    try {
      $cursorData = json_decode($jsonCursor, true, flags: JSON_THROW_ON_ERROR);

      if (!is_array($cursorData) || !isset($cursorData['timestamp'], $cursorData['id'])) {
        return null;
      }

      return new self($cursorData['timestamp'], $cursorData['id']);
    } catch (JsonException|InvalidArgumentException) {
      return null;
    }
  }

  /**
   * @return string
   * @throws JsonException
   */
  public function encode(): string {
    return base64_encode(json_encode([
      'timestamp' => $this->timestamp,
      'id' => $this->id
    ], JSON_THROW_ON_ERROR));
  }

  /**
   * @param self $other
   * @return bool
   */
  public function equals(self $other): bool {
    return $this->timestamp === $other->timestamp && $this->id === $other->id;
  }

  /**
   * @return DateTime|null
   */
  public function toDateTime(): ?DateTime {
    try {
      return new DateTime($this->timestamp);
    } catch (Exception) {
      return null;
    }
  }

  /**
   * @return string
   */
  public function toJson(): string {
    return json_encode([
      'timestamp' => $this->timestamp,
      'id' => $this->id
    ], JSON_THROW_ON_ERROR);
  }

  /**
   * @param string $id
   * @throws InvalidArgumentException
   */
  private function validateId(string $id): void {
    if (empty($id)) {
      throw new InvalidArgumentException('Cursor ID cannot be empty');
    }

    if (strlen($id) > 255) {
      throw new InvalidArgumentException('Cursor ID cannot exceed 255 characters');
    }
  }

  /**
   * @param string $timestamp
   * @throws InvalidArgumentException
   */
  private function validateTimestamp(string $timestamp): void {
    if (empty($timestamp)) {
      throw new InvalidArgumentException('Cursor timestamp cannot be empty');
    }

    if (!DateTime::createFromFormat('Y-m-d H:i:s.u', $timestamp)) {
      throw new InvalidArgumentException(
        'Cursor timestamp must be in Y-m-d H:i:s.u format, got: ' . $timestamp
      );
    }
  }

  /**
   * @throws JsonException
   */
  public function __toString(): string {
    return $this->encode();
  }
}
