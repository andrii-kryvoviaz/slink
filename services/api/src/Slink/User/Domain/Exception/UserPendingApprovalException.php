<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class UserPendingApprovalException extends SpecificationException {
  public function __construct(
    private readonly string $userId,
  ) {
    parent::__construct('Your account is pending approval.');
  }

  #[\Override]
  function getProperty(): string {
    return 'approval';
  }

  #[\Override]
  public function toPayload(): array {
    return ['userId' => $this->userId];
  }
}
