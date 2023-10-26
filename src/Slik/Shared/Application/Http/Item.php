<?php

declare(strict_types=1);

namespace Slik\Shared\Application\Http;

final readonly class Item {
  private function __construct(
    public string $type,
    public array|object $resource,
    public array $relationships = []
  ) {
  }
  
  public static function fromPayload(string $type, array $payload, array $relations = []): self {
    return new self(
      self::typeFromString($type),
      $payload,
      $relations
    );
  }
  
  public static function fromEntity(object $entity, array $relations = []): self {
    return new self(
      self::type($entity),
      $entity,
      $relations
    );
  }
  
  private static function type(object $model): string {
    return self::typeFromString($model::class);
  }
  
  private static function typeFromString(string $type): string {
    $path = \explode('\\', $type);
    
    return \array_pop($path);
  }
}