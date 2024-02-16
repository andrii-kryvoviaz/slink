<?php

declare(strict_types=1);

namespace Slink\User\Domain\Contracts;

interface UserInterface {
  /**
   * @return string
   */
  public function getIdentifier(): string;
  
  /**
   * @return array<string>
   */
  public function getRoles(): array;
}