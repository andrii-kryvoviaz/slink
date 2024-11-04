<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\User;

use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;

final readonly class PasswordSettings extends AbstractCompoundValueObject {
  
  /**
   * @param int $minLength
   * @param int $requirements
   */
  public function __construct(
    private int $minLength,
    private int $requirements
  ) {}
  
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