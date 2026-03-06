<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

final class MissingIdTokenException extends \RuntimeException {
  public function __construct(string $slug) {
    parent::__construct(
      message: sprintf('OIDC provider "%s" did not return an id_token in the token response.', $slug),
    );
  }
}
