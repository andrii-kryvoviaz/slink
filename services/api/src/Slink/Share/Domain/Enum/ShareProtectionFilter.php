<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Enum;

enum ShareProtectionFilter: string {
  case Any = 'any';
  case PasswordProtected = 'passwordProtected';
  case NoPassword = 'noPassword';
}
