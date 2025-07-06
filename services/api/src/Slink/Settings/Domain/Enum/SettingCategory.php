<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Enum;

use Slink\Settings\Domain\ValueObject\AbstractSettingsValueObject;
use Slink\Shared\Domain\Enum\ValidatorAwareEnumTrait;

enum SettingCategory: string {
  use ValidatorAwareEnumTrait;
  
  case User = 'user';
  case Image = 'image';
  case Storage = 'storage';
  case Access = 'access';
  
  public function getCategoryKey(): string {
    return $this->value;
  }
  
  /**
   * @return class-string<AbstractSettingsValueObject>
   */
  public function getSettingsCategoryRootClass(): string {
    // @phpstan-ignore-next-line
    return 'Slink\Settings\Domain\ValueObject\\' . ucfirst($this->value) . '\\' . ucfirst($this->value) . 'Settings';
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return AbstractSettingsValueObject
   */
  public function createSettingsCategoryRoot(array $payload): AbstractSettingsValueObject {
    $class = $this->getSettingsCategoryRootClass();
    
    return $class::fromPayload($payload);
  }
}
