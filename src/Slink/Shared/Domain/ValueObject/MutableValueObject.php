<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\ValueObject;

use Slink\Shared\Domain\DataStructures\HashMap;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\Date\DateTime;

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
    if(!isset($this->_updates)) {
      $this->_updates = HashMap::fromArray([]);
      return;
    }
    
    foreach ($this->_updates->toArray() as $property => $value) {
      $this->$property = $value;
    }
    
    $this->_updates->clear();
  }
}