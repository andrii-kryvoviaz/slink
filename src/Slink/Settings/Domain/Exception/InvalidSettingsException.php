<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Exception;

final class InvalidSettingsException extends \RuntimeException {
  public function __construct(string $message = 'Invalid settings configuration.') {
      parent::__construct($message);
  }
}