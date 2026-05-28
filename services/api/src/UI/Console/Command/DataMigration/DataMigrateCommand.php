<?php

declare(strict_types=1);

namespace UI\Console\Command\DataMigration;

use Slink\Shared\Infrastructure\DataMigration\DataMigrationRunner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
  name: 'data:migrate',
  description: 'Execute pending data migrations'
)]
final class DataMigrateCommand extends Command {
  public function __construct(
    private readonly DataMigrationRunner $runner,
  ) {
    parent::__construct();
  }

  protected function configure(): void {
    $this->addOption(
      'down',
      null,
      InputOption::VALUE_NONE,
      'Roll back the last executed migration'
    );

    $this->addOption(
      'dry-run',
      null,
      InputOption::VALUE_NONE,
      'Show what would be executed without making changes'
    );
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);
    $isDryRun = $input->getOption('dry-run');
    $isDown = $input->getOption('down');

    if ($isDryRun) {
      $io->note('Running in DRY RUN mode - no changes will be made');
    }

    if ($isDown) {
      return $this->rollback($io, $isDryRun);
    }

    return $this->migrate($io, $isDryRun);
  }

  private function migrate(SymfonyStyle $io, bool $isDryRun): int {
    $pending = $this->runner->getPending();

    if (empty($pending)) {
      $io->success('No pending data migrations');
      return Command::SUCCESS;
    }

    $io->note(sprintf('Found %d pending data migration(s)', count($pending)));

    foreach ($pending as $migration) {
      $io->writeln(sprintf('  Executing %s: %s', get_class($migration), $migration->getDescription()));

      if (!$isDryRun) {
        try {
          $this->runner->execute($migration);
        } catch (\Throwable $e) {
          $io->error(sprintf('Failed to execute %s: %s', get_class($migration), $e->getMessage()));
          return Command::FAILURE;
        }
      }
    }

    if ($isDryRun) {
      $io->info(sprintf('DRY RUN: Would execute %d data migration(s)', count($pending)));
    } else {
      $io->success(sprintf('Successfully executed %d data migration(s)', count($pending)));
    }

    return Command::SUCCESS;
  }

  private function rollback(SymfonyStyle $io, bool $isDryRun): int {
    $executed = $this->runner->getExecuted();

    if (empty($executed)) {
      $io->warning('No executed data migrations to roll back');
      return Command::SUCCESS;
    }

    $lastVersion = $executed[0]['version'];
    $migration = $this->runner->findByVersion($lastVersion);

    if ($migration === null) {
      $io->error(sprintf('Migration class %s no longer exists', $lastVersion));
      return Command::FAILURE;
    }

    $io->writeln(sprintf('  Rolling back %s: %s', get_class($migration), $migration->getDescription()));

    if (!$isDryRun) {
      try {
        $this->runner->rollback($migration);
      } catch (\Throwable $e) {
        $io->error(sprintf('Failed to roll back %s: %s', get_class($migration), $e->getMessage()));
        return Command::FAILURE;
      }
    }

    if ($isDryRun) {
      $io->info(sprintf('DRY RUN: Would roll back %s', $lastVersion));
    } else {
      $io->success(sprintf('Successfully rolled back %s', $lastVersion));
    }

    return Command::SUCCESS;
  }
}
