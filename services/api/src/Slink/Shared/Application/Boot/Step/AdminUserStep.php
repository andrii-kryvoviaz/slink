<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot\Step;

use Slink\Shared\Application\Boot\BootCategory;
use Slink\Shared\Application\Boot\BootContext;
use Slink\Shared\Application\Boot\BootResult;
use Slink\Shared\Application\Boot\BootStepInterface;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\User\Application\Command\CreateAdminUser\CreateAdminUserCommand;
use Slink\User\Domain\Factory\AdminUserFactory;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 50)]
final readonly class AdminUserStep implements BootStepInterface {
  public function __construct(
    private AdminUserFactory $adminUserFactory,
    private CommandBusInterface $commandBus,
  ) {}

  public function label(): string {
    return 'admin user';
  }

  public function category(): BootCategory {
    return BootCategory::Boot;
  }

  public function run(BootContext $context): BootResult {
    if ($this->adminUserFactory->isMissingPassword()) {
      return BootResult::warn('env:ADMIN_PASSWORD missing');
    }

    if (!$this->adminUserFactory->hasValidConfiguration()) {
      return BootResult::skipped();
    }

    if ($this->adminUserFactory->adminAlreadyExists()) {
      return BootResult::upToDate(sprintf('%s', $this->adminUserFactory->getAdminEmail()));
    }

    try {
      $this->commandBus->handle(new CreateAdminUserCommand());
    } catch (\Throwable $e) {
      return BootResult::fail($e->getMessage());
    }

    return BootResult::applied(sprintf('%s created', $this->adminUserFactory->getAdminEmail()));
  }
}
