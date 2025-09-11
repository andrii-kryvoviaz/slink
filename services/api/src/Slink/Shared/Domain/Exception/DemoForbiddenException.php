<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Exception;

final class DemoForbiddenException extends SpecificationException {
  
  public function __construct(string $message = 'This action is not allowed in demo mode') {
    parent::__construct($message);
  }
  
  #[\Override]
  function getProperty(): string {
    return 'message';
  }
}
