<?php

declare(strict_types=1);

namespace Slik\Shared\Domain\ValueObject;

use Slik\Shared\Domain\DataStructures\HashMap;
use Slik\Shared\Domain\Exception\DateTimeException;

trait MutableValueObject {
  /**
   * @var HashMap
   */
  private readonly HashMap $_updates;
  
  /**
   * @param string $name
   * @param mixed $value
   * @return static
   */
  private function markForUpdate(string $name, mixed $value): static {
    if($value === null) {
      return $this;
    }
    
    if(!isset($this->_updates)) {
      $this->_updates = HashMap::fromArray([]);
    }
    
    $this->_updates->set($name, $value);
    
    try {
      if (property_exists(static::class, 'updatedAt')) {
        $this->_updates->set('updatedAt', DateTime::now());
      }
    } catch (DateTimeException) {};
    
    return $this;
  }
  
  /**
   * @return void
   */
  public function __clone(): void {
    foreach ($this->_updates->toArray() as $property => $value) {
      $this->$property = $value;
    }
    
    $this->_updates->clear();
  }
}