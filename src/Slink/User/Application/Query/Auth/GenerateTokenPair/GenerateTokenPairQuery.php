<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\Auth\GenerateTokenPair;

use Slink\Shared\Application\Query\QueryInterface;

final readonly class GenerateTokenPairQuery implements QueryInterface {
  public function __construct(
    private string $email,
  ) {}
  
  public function getEmail(): string {
    return $this->email;
  }
}