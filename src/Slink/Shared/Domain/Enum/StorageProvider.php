<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Enum;

enum StorageProvider: string {
  case Local = 'local';
  case SmbShare = 'smb';
  case AmazonS3 = 's3';
  
  public function equals(StorageProvider $type): bool {
    return $this->value === $type->value;
  }
}
