<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Enum;

use Slink\Settings\Domain\ValueObject\Storage\LocalStorageSettings;
use Slink\Settings\Domain\ValueObject\Storage\SmbStorageSettings;
use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;

enum StorageProvider: string {
  case Local = 'local';
  case SmbShare = 'smb';
  
  public function equals(StorageProvider $type): bool {
    return $this->value === $type->value;
  }
}
