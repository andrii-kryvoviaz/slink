<?php

declare(strict_types=1);

namespace UI\Console\Command\Demo;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\User\Application\Command\CreateDemoUser\CreateDemoUserCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
  name: 'slink:demo:init',
  description: 'Initializes demo mode by creating the demo user'
)]
final class InitDemoCommand extends Command {

  public function __construct(
    private readonly CommandBusInterface $commandBus,
    private readonly ConfigurationProviderInterface $configurationProvider
  ) {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    if (!$this->configurationProvider->get('demo.enabled')) {
      $io->error('Demo mode is not enabled');
      return Command::FAILURE;
    }

    $io->info('Initializing demo environment...');

    try {
      $command = new CreateDemoUserCommand(
        username: $this->configurationProvider->get('demo.demoUsername'),
        password: $this->configurationProvider->get('demo.demoPassword'),
        displayName: $this->configurationProvider->get('demo.demoDisplayName')
      );

      $this->commandBus->handle($command);
      $io->success('Demo user has been created successfully');
      
      $io->table(
        ['Setting', 'Value'],
        [
          ['Username', $this->configurationProvider->get('demo.demoUsername')],
          ['Password', $this->configurationProvider->get('demo.demoPassword')],
          ['Display Name', $this->configurationProvider->get('demo.demoDisplayName')],
          ['Reset Interval', $this->configurationProvider->get('demo.resetIntervalMinutes') . ' minutes'],
        ]
      );
      
      return Command::SUCCESS;
    } catch (\Exception $e) {
      $io->error('Failed to initialize demo environment: ' . $e->getMessage());
      return Command::FAILURE;
    }
  }
}
