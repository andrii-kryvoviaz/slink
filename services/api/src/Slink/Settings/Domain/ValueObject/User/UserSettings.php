<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\User;

use Ramsey\Uuid\Uuid;
use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\Exception\InvalidAutoRedirectProviderException;
use Slink\Settings\Domain\ValueObject\AbstractSettingsValueObject;

final readonly class UserSettings extends AbstractSettingsValueObject {
  /**
   * @param bool $approvalRequired
   * @param bool $allowRegistration
   * @param PasswordSettings $password
   * @param string|null $autoRedirectProviderId
   */
  private function __construct(
    private bool $approvalRequired,
    private bool $allowRegistration,
    private PasswordSettings $password,
    private ?string $autoRedirectProviderId,
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
      'autoRedirectProviderId' => $this->autoRedirectProviderId,
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
      PasswordSettings::fromPayload($payload['password']),
      self::normalizeAutoRedirectProviderId($payload['autoRedirectProviderId'] ?? null),
    );
  }
  
  private static function normalizeAutoRedirectProviderId(mixed $value): ?string {
    if ($value === null) {
      return null;
    }
    
    if (!is_string($value)) {
      throw new InvalidAutoRedirectProviderException();
    }
    
    $id = trim($value);
    
    if ($id === '') {
      return null;
    }
    
    if (!Uuid::isValid($id)) {
      throw new InvalidAutoRedirectProviderException();
    }
    
    return $id;
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
  
  public function getAutoRedirectProviderId(): ?string {
    return $this->autoRedirectProviderId;
  }
}