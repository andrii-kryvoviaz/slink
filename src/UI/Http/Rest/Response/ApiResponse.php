<?php

declare(strict_types=1);

namespace UI\Http\Rest\Response;

use Slik\Shared\Application\Http\Collection;
use Slik\Shared\Application\Http\Item;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse extends JsonResponse {
  
  /**
   * @param array<string, mixed> $payload
   * @param int $status
   * @return self
   */
  public static function fromPayload(array $payload, int $status): self {
    return new self($payload, $status);
  }
  
  /**
   * @param int $status
   * @return self
   */
  public static function empty(int $status = self::HTTP_NO_CONTENT): self {
    return new self(null, $status);
  }
  
  /**
   * @param string|null $id
   * @param string|null $location
   * @return self
   */
  public static function created(string $id = null, string $location = null): self {
    return new self(
      $id ? ['id' => $id] : null,
      self::HTTP_CREATED,
      ($location) ? ['location' => $location] : []
    );
  }
  
  /**
   * @param Item $resource
   * @param int $status
   * @return self
   */
  public static function one(Item $resource, int $status = self::HTTP_OK): self {
    return new self(
      [
        'data' => self::model($resource),
        ...(empty($resource->relationships) ? [] : ['relationships' => self::relations($resource->relationships)]),
      ],
      $status
    );
  }
  
  /**
   * @param Collection $collection
   * @param int $status
   * @return self
   */
  public static function collection(Collection $collection, int $status = self::HTTP_OK): self {
    /**
     * @psalm-suppress MissingClosureParamType
     *
     * @param array|Item $data
     *
     * @return array<string, mixed>|object|string
     */
    $transformer = fn(array|Item $data): array|object|string => $data instanceof Item ? self::model($data) : $data;
    
    $resources = \array_map($transformer, $collection->data);
    
    return new self(
      [
        'meta' => [
          'size' => $collection->limit,
          'page' => $collection->page,
          'total' => $collection->total,
        ],
        'data' => $resources,
      ],
      $status
    );
  }
  
  /**
   * @param Item $item
   * @return array<string, mixed>|object|string
   */
  private static function model(Item $item): array|object|string {
    return $item->resource;
  }
  
  /**
   * @param Item[] $relations
   * @return array<string, mixed>
   */
  private static function relations(array $relations): array {
    $result = [];
    
    foreach ($relations as $relation) {
      $result[$relation->type] = [
        'data' => self::model($relation),
      ];
    }
    
    return $result;
  }
  
}