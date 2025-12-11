<?php

declare(strict_types=1);

namespace UI\Console\Command\Admin;

use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\User\Application\Command\CreateAdminUser\CreateAdminUserCommand;
use Slink\User\Domain\Factory\AdminUserFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
  name: 'slink:admin:init',
  description: 'Creates an admin user from environment variables if configured'
)]
final class InitAdminCommand extends Command {
  public function __construct(
    private readonly CommandBusInterface $commandBus,
    private readonly AdminUserFactory $adminUserFactory
  ) {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    if ($this->adminUserFactory->isMissingPassword()) {
      $io->warning('ADMIN_EMAIL is set but ADMIN_PASSWORD is empty. Skipping admin user creation.');
      return Command::SUCCESS;
    }

    if (!$this->adminUserFactory->hasValidConfiguration()) {
      return Command::SUCCESS;
    }

    if ($this->adminUserFactory->adminAlreadyExists()) {
      return Command::SUCCESS;
    }

    $io->info('Creating admin user...');

    try {
      $this->commandBus->handle(new CreateAdminUserCommand());

      $io->success('Admin user has been created successfully');

      $io->table(
        ['Setting', 'Value'],
        [
          ['Username', $this->adminUserFactory->getAdminUsername()],
          ['Email', $this->adminUserFactory->getAdminEmail()],
        ]
      );

      return Command::SUCCESS;
    } catch (\Exception $e) {
      $io->error('Failed to create admin user: ' . $e->getMessage());
      return Command::FAILURE;
    }
  }
}
