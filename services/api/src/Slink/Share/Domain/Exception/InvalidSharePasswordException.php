<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class InvalidSharePasswordException extends HttpException {
  public function __construct() {
    parent::__construct(Response::HTTP_UNAUTHORIZED, 'invalid_password');
  }
}
