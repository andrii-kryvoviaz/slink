<?php

declare(strict_types=1);

namespace Slink\Settings\Infrastructure\ReadModel\View;

use Slink\Settings\Domain\Enum\SettingType;
use Slink\Settings\Infrastructure\ReadModel\Repository\SettingsRepository;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: '`settings`')]
#[ORM\Entity(repositoryClass: SettingsRepository::class)]
final class SettingsView extends AbstractView {
  
  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    public string $key,
    
    #[ORM\Column(type: 'text')]
    public string $value,
    
    #[ORM\Column(enumType: SettingType::class, options: ['default' => SettingType::String])]
    public string $type,
  ) {
  }
}