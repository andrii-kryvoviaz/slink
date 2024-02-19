<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject;

use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;

final readonly class UserSettings extends AbstractCompoundValueObject {
  private function __construct(
    private bool $approvalRequired,
  ) {}
  
  /**
   * @return array<string, mixed>
   */
  #[\Override]
  public function toPayload(): array {
    return [
      'approvalRequired' => $this->approvalRequired,
    ];
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  #[\Override]
  public static function fromPayload(array $payload): static {
    return new self(
      $payload['approvalRequired'],
    );
  }
}