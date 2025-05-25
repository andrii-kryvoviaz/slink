<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Specification;

abstract class AbstractSpecification {
    abstract public function isSatisfiedBy(mixed $value): bool;
}
