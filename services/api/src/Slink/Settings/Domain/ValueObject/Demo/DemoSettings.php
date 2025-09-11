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
    
    #[Assert\Length(min: 3, max: 50)]
    public string $demoUsername = 'demo',
    
    #[Assert\Length(min: 3, max: 50)]
    public string $demoPassword = 'demo',
    
    #[Assert\Length(min: 3, max: 100)]
    public string $demoDisplayName = 'Demo User'
  ) {
  }

  /**
   * @return array<string>
   */
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
      'demoUsername' => $this->demoUsername,
      'demoPassword' => $this->demoPassword,
      'demoDisplayName' => $this->demoDisplayName,
    ];
  }

  #[\Override]
  public static function fromPayload(array $payload): static {
    return new self(
      enabled: $payload['enabled'] ?? false,
      demoUsername: $payload['demoUsername'] ?? 'demo',
      demoPassword: $payload['demoPassword'] ?? 'demo',
      demoDisplayName: $payload['demoDisplayName'] ?? 'Demo User'
    );
  }

  public function getCategoryKey(): string {
    return 'demo';
  }

  /**
   * @return array<string, mixed>
   */
  public function toArray(): array {
    return $this->toPayload();
  }
}
