<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\User\FindUserById;

use Slink\Shared\Application\Query\QueryInterface;

final readonly class FindUserByIdQuery implements QueryInterface {
  /**
   * @param string $id
   * @param array<string> $groups
   */
  public function __construct(
    private string $id,
    private array $groups = ['public']
  ) {
  }
  
  /**
   * @return string
   */
  public function getId(): string {
    return $this->id;
  }
  
  /**
   * @return array<string>
   */
  public function getGroups(): array {
    return $this->groups;
  }
}