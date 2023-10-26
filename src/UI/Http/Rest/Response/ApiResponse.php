<?php

declare(strict_types=1);

namespace UI\Http\Rest\Response;

use Slik\Shared\Application\Http\Collection;
use Slik\Shared\Application\Http\Item;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse extends JsonResponse {
  
  public static function fromPayload(array $payload, int $status): self {
    return new self($payload, $status);
  }
  
  public static function empty(int $status): self {
    return new self(null, $status);
  }
  
  public static function created(string $location = null): self {
    return new self(
      null,
      self::HTTP_CREATED,
      ($location) ? ['location' => $location] : []
    );
  }
  
  public static function one(Item $resource, int $status = self::HTTP_OK): self {
    return new self(
      [
        'data' => self::model($resource),
        ...(empty($resource->relationships) ? [] : ['relationships' => self::relations($resource->relationships)]),
      ],
      $status
    );
  }
  
  public static function collection(Collection $collection, int $status = self::HTTP_OK): self {
    /**
     * @psalm-suppress MissingClosureParamType
     *
     * @param array|Item $data
     *
     * @return array
     */
    $transformer = fn(array|Item $data): array => $data instanceof Item ? self::model($data) : $data;
    
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
  
  private static function model(Item $item): array {
    return $item->resource;
  }
  
  /**
   * @param Item[] $relations
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