<?php

declare(strict_types=1);

namespace UI\Console\Command\User;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Application\Command\CreateUser\CreateUserCommand as CreateUser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;

#[AsCommand(
  name: 'user:create',
  description: 'Create a new user account'
)]
final class CreateUserCommand extends Command {
  use CommandTrait;

  public function configure(): void {
    $this
      ->addOption('email', null, InputOption::VALUE_REQUIRED, 'User email address')
      ->addOption('username', null, InputOption::VALUE_REQUIRED, 'Username')
      ->addOption('display-name', null, InputOption::VALUE_OPTIONAL, 'Display name (defaults to username)')
      ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'User password (will be prompted if not provided)')
      ->addOption('activate', 'a', InputOption::VALUE_NONE, 'Activate the user account immediately');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    /** @var QuestionHelper $helper */
    $helper = $this->getHelper('question');

    $email = $input->getOption('email');
    $username = $input->getOption('username');
    $displayName = $input->getOption('display-name') ?? $username;

    $password = $input->getOption('password');
    if ($password === null) {
      $passwordQuestion = new Question('Enter password: ');
      $passwordQuestion->setHidden(true);
      $passwordQuestion->setHiddenFallback(false);
      $password = $helper->ask($input, $output, $passwordQuestion);
    }

    $activate = $input->getOption('activate');

    try {
      $command = new CreateUser(
        email: $email,
        password: $password,
        username: $username,
        displayName: $displayName,
        activate: $activate
      );

      $this->handle($command);

      $output->writeln(sprintf(
        '<info>User `%s` (%s) has been created successfully ✓</info>',
        $email,
        $username
      ));

      if ($activate) {
        $output->writeln('<info>User account has been activated</info>');
      }

      return Command::SUCCESS;
    } catch (\Exception $e) {
      $output->writeln(sprintf('<error>Error: %s</error>', $e->getMessage()));
      return Command::FAILURE;
    }
  }
}
