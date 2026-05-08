<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('slink.boot_step')]
interface BootStepInterface {
  public function label(): string;

  public function category(): BootCategory;

  public function run(BootContext $context): BootResult;
}
