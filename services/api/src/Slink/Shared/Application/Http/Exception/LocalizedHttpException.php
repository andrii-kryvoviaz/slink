<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Http\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

final class LocalizedHttpException extends HttpException {
  /**
   * @var array<string, scalar|null>
   */
  private readonly array $params;

  /**
   * @param array<string, scalar|null> $params
   */
  public function __construct(
    int $statusCode,
    string $message = '',
    ?Throwable $previous = null,
    array $headers = [],
    int $code = 0,
    array $params = [],
  ) {
    parent::__construct($statusCode, $message, $previous, $headers, $code);
    $this->params = $params;
  }

  /**
   * @return array<string, scalar|null>
   */
  public function getParams(): array {
    return $this->params;
  }
}
