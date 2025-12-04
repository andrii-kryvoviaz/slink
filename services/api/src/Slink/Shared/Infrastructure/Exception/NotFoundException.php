<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Exception;

use Slink\Shared\Domain\Exception\NotFoundException as DomainNotFoundException;

class NotFoundException extends DomainNotFoundException {
}