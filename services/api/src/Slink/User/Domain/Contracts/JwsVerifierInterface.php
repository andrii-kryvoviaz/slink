<?php

declare(strict_types=1);

namespace Slink\User\Domain\Contracts;

use Slink\User\Domain\ValueObject\OAuth\IdToken;
use Slink\User\Domain\ValueObject\OAuth\JwksUri;
use Slink\User\Domain\ValueObject\OAuth\TokenClaims;

interface JwsVerifierInterface {
  public function verify(IdToken $idToken, JwksUri $jwksUri): TokenClaims;
}
