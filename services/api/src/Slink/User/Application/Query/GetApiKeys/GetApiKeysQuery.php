<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\GetApiKeys;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class GetApiKeysQuery implements QueryInterface {
  public function __construct(
    private ID $userId
  ) {}

  public function getUserId(): ID {
    return $this->userId;
  }
}
