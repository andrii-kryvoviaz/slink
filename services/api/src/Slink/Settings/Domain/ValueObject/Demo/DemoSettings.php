<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Demo;

use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\ValueObject\AbstractSettingsValueObject;
use Slink\Shared\Infrastructure\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[Groups(['public'])]
final readonly class DemoSettings extends AbstractSettingsValueObject {

  public function __construct(
    #[Assert\Type('bool')]
    #[Groups(['public'])]
    public bool $enabled = false,
    
    #[Assert\Type('bool')]
    public bool $protectDemoUser = true,
    
    #[Assert\Type('integer')]
    #[Assert\Range(min: 1, max: 1440)]
    public int $resetIntervalMinutes = 60,
    
    #[Assert\Length(min: 3, max: 50)]
    public string $demoUsername = 'demo',
    
    #[Assert\Length(min: 3, max: 50)]
    public string $demoPassword = 'demo',
    
    #[Assert\Length(min: 3, max: 100)]
    public string $demoDisplayName = 'Demo User'
  ) {
    parent::__construct();
  }

  public static function getPayloadValidationGroups(): array {
    return ['demo'];
  }

  #[\Override]
  public function getSettingsCategory(): SettingCategory {
    return SettingCategory::Demo;
  }

  #[\Override]
  public function toPayload(): array {
    return [
      'enabled' => $this->enabled,
      'protectDemoUser' => $this->protectDemoUser,
      'resetIntervalMinutes' => $this->resetIntervalMinutes,
      'demoUsername' => $this->demoUsername,
      'demoPassword' => $this->demoPassword,
      'demoDisplayName' => $this->demoDisplayName,
    ];
  }

  #[\Override]
  public static function fromPayload(array $payload): static {
    return new self(
      enabled: $payload['enabled'] ?? false,
      protectDemoUser: $payload['protectDemoUser'] ?? true,
      resetIntervalMinutes: $payload['resetIntervalMinutes'] ?? 60,
      demoUsername: $payload['demoUsername'] ?? 'demo',
      demoPassword: $payload['demoPassword'] ?? 'demo',
      demoDisplayName: $payload['demoDisplayName'] ?? 'Demo User'
    );
  }

  public function getCategoryKey(): string {
    return 'demo';
  }

  public function toArray(): array {
    return $this->toPayload();
  }
}
