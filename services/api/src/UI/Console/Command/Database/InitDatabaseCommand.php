<?php

namespace UI\Console\Command\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
  name: 'slink:database:init',
  description: 'Create a database if it does not exist and the platform supports it',
)]
final class InitDatabaseCommand extends Command {
  public function __construct(
    private readonly ManagerRegistry $registry,
  ) {
    parent::__construct();
  }

  protected function configure(): void {
    $this->addOption('connection', 'c', InputOption::VALUE_REQUIRED, 'The connection to use for this command');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $connection = $this->registry->getConnection($input->getOption('connection'));
    assert($connection instanceof Connection);

    if ($connection->getDatabasePlatform() instanceof SQLitePlatform) {
      $io->info('SQLite platform detected. Skipping database creation.');
      return Command::SUCCESS;
    }

    $createDatabaseInput = new ArrayInput([
      'command' => 'doctrine:database:create',
      '--connection' => $input->getOption('connection'),
      '--if-not-exists' => true,
    ]);

    $createDatabaseInput->setInteractive($input->isInteractive());

    return $this->getApplication()->doRun($createDatabaseInput, $output);
  }
}
