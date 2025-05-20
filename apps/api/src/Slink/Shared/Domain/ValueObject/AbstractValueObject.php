<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\ValueObject;

use Slink\Shared\Domain\Strategy\JsonSerializableBehaviour;

abstract readonly class AbstractValueObject implements \JsonSerializable, \Stringable {
  use JsonSerializableBehaviour;
  
  /**
   * @param ?AbstractValueObject $other
   * @return bool
   */
  public function equals(?self $other): bool {
    if ($other === null) {
      return false;
    }
    
    return array_all($this->getProperties(), fn($value, $property) => $value === $other->{'get' . \ucfirst($property)}());
  }
  
  /**
   * @return string
   */
  public function __toString(): string {
    return $this->toString();
  }
  
  /**
   * @return string
   */
  public function toString(): string {
    return (string) \json_encode(
      $this->jsonSerialize()
    );
  }
}
