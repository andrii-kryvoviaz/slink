<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;


use Slink\Shared\Domain\Exception\SpecificationException;

final class DemoUserProtectionException extends SpecificationException {

  /**
   * @return string
   */
  function getProperty(): string {
    return 'demoUserProtection';
  }
}
