<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot\Step;

use Slink\Shared\Application\Boot\BootCategory;
use Slink\Shared\Application\Boot\BootContext;
use Slink\Shared\Application\Boot\BootResult;
use Slink\Shared\Application\Boot\BootStepInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsTaggedItem(priority: 90)]
final readonly class EventStoreSchemaStep implements BootStepInterface {
  public function __construct(
    #[Autowire('%env(default::resolve:ES_DATABASE_URL)%')]
    private ?string $databaseUrl,
  ) {}

  public function label(): string {
    return 'event-store setting';
  }

  public function category(): BootCategory {
    return BootCategory::Boot;
  }

  public function run(BootContext $context): BootResult {
    if ($this->databaseUrl === null || $this->databaseUrl === '') {
      return BootResult::skipped();
    }

    return BootResult::warn('env:ES_DATABASE_URL is deprecated and ignored');
  }
}
