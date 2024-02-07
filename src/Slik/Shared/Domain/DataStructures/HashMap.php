<?php

declare(strict_types=1);

namespace Slik\Shared\Domain\DataStructures;

class HashMap {
  /**
   * @param array<string|int, mixed> $_values
   */
  public function __construct(private array $_values = []) {
  }
  
  /**
   * @param array<string|int, mixed> $array
   * @return self
   */
  static public function fromArray(array $array): self {
    return new self($array);
  }
  
  /**
   * @param string $key
   * @param mixed $value
   * @return void
   */
  public function set(string $key, mixed $value): void {
    $this->_values[$key] = $value;
  }
  
  /**
   * @param string $key
   * @return mixed
   */
  public function get(string $key): mixed {
    return $this->_values[$key];
  }
  
  /**
   * @param string $key
   * @return bool
   */
  public function has(string $key): bool {
    return isset($this->_values[$key]);
  }
  
  /**
   * @param string $key
   * @return void
   */
  public function remove(string $key): void {
    unset($this->_values[$key]);
  }
  
  /**
   * @return void
   */
  public function clear(): void {
    $this->_values = [];
  }
  
  /**
   * @return array<int|string, mixed>
   */
  public function toArray(): array {
    return $this->_values;
  }
  
  /**
   * @return int
   */
  public function count(): int {
    return count($this->_values);
  }
  
  /**
   * @return array<int, int|string>
   */
  public function keys(): array {
    return array_keys($this->_values);
  }
  
  /**
   * @return array<int, mixed>
   */
  public function values(): array {
    return array_values($this->_values);
  }
  
  /**
   * @param HashMap $map
   * @return void
   */
  public function merge(HashMap $map): void {
    $this->_values = array_merge($this->_values, $map->toArray());
  }
}