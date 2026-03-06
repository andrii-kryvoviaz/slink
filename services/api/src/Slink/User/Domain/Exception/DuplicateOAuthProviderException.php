<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

final class DuplicateOAuthProviderException extends SpecificationException {
  public function __construct(string $slug) {
    parent::__construct(sprintf('OAuth provider "%s" already exists', $slug));
  }

  public function getProperty(): string {
    return 'slug';
  }
}
