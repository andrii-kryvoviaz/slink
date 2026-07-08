<?php

declare(strict_types=1);

namespace Slink\Settings\Application\Query\RetrieveApplicationSettings;

use Slink\Media\Domain\Enum\MediaFormat;
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
      return [
        ...$this->settingsService->all(),
        'meta' => [
          'mediaFormats' => $this->mediaFormatRegistry(),
        ],
      ];
    }

    $settingsValue = $this->settingsService->get($settingsKey);

    if (is_array($settingsValue)) {
      return $settingsValue;
    }

    return [
      $settingsKey => $settingsValue,
    ];
  }

  /**
   * @return list<array{value: string, label: string, bit: int}>
   */
  private function mediaFormatRegistry(): array {
    return array_map(
      static fn (MediaFormat $format): array => [
        'value' => $format->value,
        'label' => $format->label(),
        'bit' => $format->bit(),
      ],
      MediaFormat::cases(),
    );
  }
}