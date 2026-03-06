<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

final class OAuthProviderNotFoundException extends \RuntimeException {
  public function __construct(string $id) {
    parent::__construct(sprintf('OAuth provider with id "%s" not found', $id));
  }
}
