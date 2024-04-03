<?php

declare(strict_types=1);

namespace UI\Http\Rest\Response;

use Slink\Shared\Application\Http\Item;
use Symfony\Component\HttpFoundation\Response;

final class ContentResponse extends Response {
  
  public static function file(Item $content, int $status = self::HTTP_OK): self {
    if(!is_string($content->resource)) {
      throw new \InvalidArgumentException('Resource must be a string');
    }
    
    return new self($content->resource, $status, ['Content-Type' => $content->type]);
  }
}