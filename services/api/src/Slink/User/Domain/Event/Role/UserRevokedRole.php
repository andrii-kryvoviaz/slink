<?php

declare(strict_types=1);

namespace Slink\User\Domain\Event\Role;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\ValueObject\Role;

final readonly class UserRevokedRole implements SerializablePayload {
  /**
   * @param ID $id
   * @param Role $role
   */
  public function __construct(
    public ID   $id,
    public Role $role
  ) {
  }
  
  /**
   * @return array<string, string>
   */
  public function toPayload(): array {
    return [
      'uuid' => $this->id->toString(),
      'role' => $this->role->getRole()
    ];
  }
  
  /**
   * @param array<string, string> $payload
   * @return static
   */
  public static function fromPayload(array $payload): static {
    return new static(
      ID::fromString($payload['uuid']),
      Role::fromString($payload['role'])
    );
  }
}