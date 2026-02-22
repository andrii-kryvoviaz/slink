<?php

declare(strict_types=1);

namespace Slink\User\Domain\Enum;

use Slink\Shared\Domain\Enum\ValidatorAwareEnumTrait;

enum OAuthProvider: string {
  use ValidatorAwareEnumTrait;

  case Google = 'google';
  case Github = 'github';
  case Authentik = 'authentik';
  case Keycloak = 'keycloak';
  case Authelia = 'authelia';
}
