<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Service;

interface ConfigurationProvider {
  /**
   * @param string $key
   * @return mixed
   */
  public function get(string $key): mixed;
  
  /**
   * @param array<string> $keys
   * @return array<int, mixed>
   */
  public function getBulk(array $keys): array;
  
  /**
   * @param string $key
   * @param mixed $value
   * @return void
   */
  public function set(string $key, mixed $value): void;
  
  /**
   * @param string $key
   * @return bool
   */
  public function has(string $key): bool;
}