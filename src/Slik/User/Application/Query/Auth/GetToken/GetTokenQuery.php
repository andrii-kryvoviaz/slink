<?php

declare(strict_types=1);

namespace Slik\User\Application\Query\Auth\GetToken;

use Slik\Shared\Application\Query\QueryInterface;

final readonly class GetTokenQuery implements QueryInterface {
  public function __construct(
    private string $email,
  ) {}
  
  public function getEmail(): string {
    return $this->email;
  }
}