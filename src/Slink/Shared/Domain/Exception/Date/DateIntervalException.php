<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Exception\Date;

final class DateIntervalException extends \LogicException {
  public function __construct(string $message) {
    parent::__construct($message);
  }
}