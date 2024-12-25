<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Enum;

enum StorageProvider: string {
  case Local = 'local';
  case SMB = 'smb';
}