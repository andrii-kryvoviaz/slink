<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Slink\Settings\Infrastructure\ReadModel\View\SettingsView;

interface SettingsRepositoryInterface extends ServiceEntityRepositoryInterface {
  /**
   * @param string $key
   * @param mixed $value
   * @return void
   */
  public function set(string $key, mixed $value): void;
  
  /**
   * @param array<string, mixed> $settings
   * @return void
   */public function setBulk(array $settings): void;
  
  /**
   * @param string $key
   * @return SettingsView
   */
  public function get(string $key): SettingsView;
  
  /**
   * @return array<int, SettingsView>
   */
  public function all(): array;
}