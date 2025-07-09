<?php

declare(strict_types=1);

namespace Slink\Settings\Application\Query\RetrievePublicApplicationSettings;

use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\ValueObject\Public\PublicApplicationSettings;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;

final readonly class RetrievePublicApplicationSettingsHandler implements QueryHandlerInterface {
  public function __construct(
    private SettingsService $settingsService,
  ) {
  }
  
  /**
   * @param RetrievePublicApplicationSettingsQuery $query
   * @param array<string> $groups
   * @return Item
   */
  public function __invoke(RetrievePublicApplicationSettingsQuery $query, array $groups = ['public']): Item {
    $allSettings = $this->settingsService->all();
    $publicSettings = PublicApplicationSettings::fromArray($allSettings);
    
    return Item::fromEntity($publicSettings, groups: $groups);
  }
}
