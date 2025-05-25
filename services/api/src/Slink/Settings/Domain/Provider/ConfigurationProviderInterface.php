<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Provider;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @template T
 */
#[AutoconfigureTag(ConfigurationProviderInterface::class)]
interface ConfigurationProviderInterface {
  /**
   * @param string $key
   * @return mixed
   */
  public function get(string $key): mixed;
  
  /**
   * @param string $key
   * @return bool
   */
  public function has(string $key): bool;
  
  /**
   * @return array<string, mixed>
   */
  public function all(): array;
}