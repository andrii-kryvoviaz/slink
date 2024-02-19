<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Enum;

use Slink\Settings\Domain\ValueObject\LocalStorageSettings;
use Slink\Settings\Domain\ValueObject\SmbStorageSettings;
use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;

enum StorageProvider: string {
  case Local = 'local';
  case SmbShare = 'smb';
  
  public function equals(StorageProvider $type): bool {
    return $this->value === $type->value;
  }
  
  /**
   * @param StorageProvider $type
   * @return class-string<AbstractCompoundValueObject>
   */
  public static function getSettingsClass(self $type): string {
    return match ($type) {
      self::Local => LocalStorageSettings::class,
      self::SmbShare => SmbStorageSettings::class
    };
  }
}
