<?php

declare(strict_types=1);

namespace Slink\Settings\Application\Command\SaveSettings;

use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Shared\Application\Command\CommandInterface;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class SaveSettingsCommand implements CommandInterface {
  /**
   * @param string $category
   * @param array<string, mixed> $settings
   */
  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Choice(callback: [SettingCategory::class, 'values'])]
    private string $category,
    
    #[Assert\NotBlank]
    private array $settings
  ) {
  }
  
  /**
   * @return string
   */
  public function getCategory(): string {
    return $this->category;
  }
  
  /**
   * @return array<string, mixed>
   */
  public function getSettings(): array {
    return $this->settings;
  }
}