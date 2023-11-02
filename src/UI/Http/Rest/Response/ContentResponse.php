<?php

declare(strict_types=1);

namespace UI\Http\Rest\Response;

use Slik\Shared\Application\Http\Item;
use Symfony\Component\HttpFoundation\Response;

final class ContentResponse extends Response {
  
  public static function file(Item $content, string $mime, int $status = self::HTTP_OK): self {
    return new self($content->resource, $status, ['Content-Type' => $mime]);
  }
}