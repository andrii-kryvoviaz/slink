<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Strategy;

use Slink\Shared\Domain\Strategy\StringTransformer\PassthroughTransformer;
use Slink\Shared\Domain\Strategy\StringTransformer\SnakeCaseTransformer;
use Slink\Shared\Domain\Strategy\StringTransformer\StringTransformer;

trait JsonSerializableBehaviour {
  /**
   * @return array<string, mixed>
   */
  public function jsonSerialize(): array {
    return $this->getProperties(SnakeCaseTransformer::class);
  }
  
  /**
   * @param class-string<StringTransformer> $nameConvertor
   * @return array<string, mixed>
   */
  private function getProperties(string $nameConvertor = PassthroughTransformer::class): array {
    if(!is_subclass_of($nameConvertor, StringTransformer::class)) {
      throw new \InvalidArgumentException('The name convertor must be a subclass of ' . StringTransformer::class);
    }
    
    $transformer = new $nameConvertor();
    
    $reflection = new \ReflectionClass(static::class);
    
    return array_reduce(
      $reflection->getProperties(),
      function ($carry, $property) use ($transformer) {
        $propertyName = $transformer->transform($property->getName());
        $carry[$propertyName] = $property->getValue($this);
        return $carry;
      },
      []
    );
  }
}