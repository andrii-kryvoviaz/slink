<?php

declare(strict_types=1);

namespace UI\Console\Command\Storage;

use Slink\Shared\Infrastructure\FileSystem\Ownership\OwnershipApplier;
use Slink\Shared\Infrastructure\FileSystem\Ownership\OwnershipPlan;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
  name: 'slink:storage:fix-ownership',
  description: 'Apply ownership and permissions to the slink storage tree',
)]
final class FixOwnershipCommand extends Command {
  public function __construct(
    private readonly OwnershipApplier $applier,
  ) {
    parent::__construct();
  }

  protected function configure(): void {
    $this->addOption('app-dir', null, InputOption::VALUE_REQUIRED, 'Application directory', \getenv('SLINK_APP_DIR') ?: '/app');
    $this->addOption('api-var-dir', null, InputOption::VALUE_REQUIRED, 'API var directory', \getenv('SLINK_API_VAR_DIR') ?: '/services/api/var');
    $this->addOption('data-dir', null, InputOption::VALUE_REQUIRED, 'Data directory', \getenv('SLINK_DATA_DIR') ?: '/data');
    $this->addOption('run-dir', null, InputOption::VALUE_REQUIRED, 'Run directory', \getenv('SLINK_RUN_DIR') ?: '/run');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $plan = OwnershipPlan::fromStoragePaths(
      (string) $input->getOption('app-dir'),
      (string) $input->getOption('api-var-dir'),
      (string) $input->getOption('data-dir'),
      (string) $input->getOption('run-dir'),
    );

    $this->applier->apply($plan);

    $io = new SymfonyStyle($input, $output);
    $io->success('Storage ownership applied.');

    return Command::SUCCESS;
  }
}
