<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Infrastructure\ReadModel\View\SettingsView;

interface SettingsRepositoryInterface extends ServiceEntityRepositoryInterface {
  /**
   * @param string $key
   * @param mixed $value
   * @param SettingCategory $category
   * @return void
   */
  public function set(string $key, mixed $value, SettingCategory $category): void;
  
  /**
   * @param array<string, mixed> $settings
   * @param SettingCategory $category
   * @return void
   */
  public function setBulk(array $settings, SettingCategory $category): void;
  
  /**
   * @param string $key
   * @return SettingsView|null
   */
  public function get(string $key): ?SettingsView;
  
  /**
   * @return array<int, SettingsView>
   */
  public function all(): array;
}