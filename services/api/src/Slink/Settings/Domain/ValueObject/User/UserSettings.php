<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\User;

use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\ValueObject\AbstractSettingsValueObject;

final readonly class UserSettings extends AbstractSettingsValueObject {
  /**
   * @param bool $approvalRequired
   * @param bool $allowRegistration
   * @param PasswordSettings $password
   */
  private function __construct(
    private bool $approvalRequired,
    private bool $allowRegistration,
    private PasswordSettings $password,
  ) {}
  
  /**
   * @return array<string, mixed>
   */
  #[\Override]
  public function toPayload(): array {
    return [
      'approvalRequired' => $this->approvalRequired,
      'allowRegistration' => $this->allowRegistration,
      'password' => $this->password->toPayload(),
    ];
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  #[\Override]
  public static function fromPayload(array $payload): static {
    return new self(
      $payload['approvalRequired'] ?? true,
      $payload['allowRegistration'] ?? true,
      PasswordSettings::fromPayload($payload['password'])
    );
  }
  
  /**
   * @return SettingCategory
   */
  #[\Override]
  public function getSettingsCategory(): SettingCategory {
    return SettingCategory::User;
  }
  
  /**
   * @return bool
   */
  public function isApprovalRequired(): bool {
    return $this->approvalRequired;
  }
  
  public function isAllowRegistration(): bool {
    return $this->allowRegistration;
  }
  
  public function getPassword(): PasswordSettings {
    return $this->password;
  }
}