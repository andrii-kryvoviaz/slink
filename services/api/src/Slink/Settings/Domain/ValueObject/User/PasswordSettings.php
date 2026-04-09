<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\User;

use Slink\Settings\Domain\Exception\InvalidPasswordMinLengthException;
use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;

final readonly class PasswordSettings extends AbstractCompoundValueObject {
  private int $minLength;
  
  /**
   * @param ?int $minLength
   * @param int $requirements
   */
  public function __construct(
    ?int $minLength,
    private int $requirements
  ) {
    if ($minLength === null) {
      throw new InvalidPasswordMinLengthException('SETTINGS_PASSWORD_LENGTH_REQUIRED');
    }

    if ($minLength < 3) {
      throw new InvalidPasswordMinLengthException('SETTINGS_PASSWORD_LENGTH_TOO_SHORT');
    }
    
    if ($minLength > 64) {
      throw new InvalidPasswordMinLengthException('SETTINGS_PASSWORD_LENGTH_TOO_LONG');
    }
    
    $this->minLength = $minLength;
  }
  
  /**
   * @inheritDoc
   */
  public function toPayload(): array {
    return [
      'minLength' => $this->minLength,
      'requirements' => $this->requirements
    ];
  }
  
  /**
   * @inheritDoc
   */
  public static function fromPayload(array $payload): static {
    return new self(
      $payload['minLength'],
      $payload['requirements']
    );
  }
  
  /**
   * @return int
   */
  public function getMinLength(): int {
    return $this->minLength;
  }
  
  /**
   * @return int
   */
  public function getRequirements(): int {
    return $this->requirements;
  }
}