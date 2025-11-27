<?php

declare(strict_types=1);

namespace Tests\Traits;

trait PrivatePropertyTrait {
  /**
   * Set a private property's value
   *
   * @param object $object
   * @param string $propertyName
   * @param mixed $value
   * @throws \ReflectionException
   */
  public function setPrivateProperty(object $object, string $propertyName, $value): void {
    $reflection = new \ReflectionClass($object);
    $property = $reflection->getProperty($propertyName);
    $property->setValue($object, $value);
  }
  
  /**
   * Get a private property's value
   *
   * @param object $object
   * @param string $propertyName
   * @return mixed
   * @throws \ReflectionException
   */
  public function getPrivateProperty(object $object, string $propertyName): mixed {
    $reflection = new \ReflectionClass($object);
    $property = $reflection->getProperty($propertyName);
    return $property->getValue($object);
  }
}