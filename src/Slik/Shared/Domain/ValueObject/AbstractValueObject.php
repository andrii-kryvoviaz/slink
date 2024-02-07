<?php

declare(strict_types=1);

namespace Slik\Shared\Domain\ValueObject;

abstract readonly class AbstractValueObject implements \JsonSerializable, \Stringable {
  abstract public function toString(): string;
  
  public function equals(self $other): bool {
    $properties = get_object_vars($this);
    
    foreach ($properties as $property => $value) {
      if ($value !== $other->{$property}) {
        return false;
      }
    }
    
    return true;
  }

  public function __toString(): string {
    return $this->toString();
  }

  public function jsonSerialize(): string {
    return $this->toString();
  }
}