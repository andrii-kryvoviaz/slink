<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Http;

use Slink\Shared\Infrastructure\Serializer\SerializerFactory;

final readonly class Item {
  /**
   * @param string $type
   * @param array<string, mixed>|object|string $resource
   * @param array<string, mixed> $relationships
   */
  private function __construct(
    public string              $type,
    public array|object|string $resource,
    public array               $relationships = []
  ) {
  }
  
  /**
   * @param string $type
   * @param array<string, mixed> $payload
   * @param array<string, mixed> $relations
   * @return self
   */
  public static function fromPayload(string $type, array $payload, array $relations = []): self {
    return new self(
      self::typeFromString($type),
      $payload,
      $relations
    );
  }
  
  /**
   * @param string $content
   * @param string $mimeType
   * @return self
   */
  public static function fromContent(string $content, string $mimeType): self {
    return new self(
      $mimeType,
      $content
    );
  }
  
  /**
   * @param object $entity
   * @param array<string, mixed> $relations
   * @param array<string> $groups
   * @return self
   */
  public static function fromEntity(object $entity, array $relations = [], array $groups = ['public'] ): self {
    try {
      /** @var array<string, mixed> $payload */
      $payload = SerializerFactory::create()->normalize($entity, context: ['groups' => $groups]);
    } catch(\Throwable $e) {
      throw new \RuntimeException($e->getMessage());
    }
    
    return self::fromPayload(self::type($entity), $payload, $relations);
  }
  
  /**
   * @param object $model
   * @return string
   */
  private static function type(object $model): string {
    return self::typeFromString($model::class);
  }
  
  /**
   * @param string $type
   * @return string
   */
  private static function typeFromString(string $type): string {
    $path = \explode('\\', $type);
    
    return \array_pop($path);
  }
}