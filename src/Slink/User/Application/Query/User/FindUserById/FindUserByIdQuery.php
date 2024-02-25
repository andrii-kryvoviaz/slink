<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\User\FindUserById;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class FindUserByIdQuery implements QueryInterface {
  use EnvelopedMessage;
  
  /**
   * @param string $id
   */
  public function __construct(
    private string $id,
  ) {
  }
  
  /**
   * @return string
   */
  public function getId(): string {
    return $this->id;
  }
}