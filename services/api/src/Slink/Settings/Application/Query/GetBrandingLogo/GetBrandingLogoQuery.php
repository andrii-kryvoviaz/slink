<?php

declare(strict_types=1);

namespace Slink\Settings\Application\Query\GetBrandingLogo;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetBrandingLogoQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    public bool $versioned = false,
  ) {
  }
}
