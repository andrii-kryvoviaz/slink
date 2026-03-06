<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\OAuth;

enum JwsAlgorithm: string {
  case RS256 = 'RS256';
  case RS384 = 'RS384';
  case RS512 = 'RS512';
  case ES256 = 'ES256';
  case ES384 = 'ES384';
  case ES512 = 'ES512';
}
