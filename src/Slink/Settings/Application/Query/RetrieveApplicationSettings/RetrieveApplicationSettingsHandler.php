<?php

declare(strict_types=1);

namespace Slink\Settings\Application\Query\RetrieveApplicationSettings;

use Slink\Settings\Application\Service\SettingsService;
use Slink\Shared\Application\Query\QueryHandlerInterface;

final readonly class RetrieveApplicationSettingsHandler implements QueryHandlerInterface {
  public function __construct(
    private SettingsService $settingsService,
  ) {
  }
  
  /**
   * @param RetrieveApplicationSettingsQuery $query
   * @return mixed
   */
  public function __invoke(RetrieveApplicationSettingsQuery $query): mixed {
    $settingsKey = $query->getKey();
    
    if ($settingsKey === null) {
      return $this->settingsService->all();
    }
    
    $settingsValue = $this->settingsService->get($settingsKey);
    
    if (is_array($settingsValue)) {
      return $settingsValue;
    }
    
    return [
      $settingsKey => $settingsValue,
    ];
  }
}