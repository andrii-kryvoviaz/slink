<?php

declare(strict_types=1);

namespace UI\Console\Command\User;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Application\Command\ResetPassword\ResetPasswordCommand as ResetPassword;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;

#[AsCommand(
  name: 'user:reset-password',
  description: 'Reset a user password by email without the current password'
)]
final class ResetPasswordCommand extends Command {
  use CommandTrait;

  public function configure(): void {
    $this
      ->addOption('email', null, InputOption::VALUE_REQUIRED, 'User email address')
      ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'New password (will be prompted if not provided)');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    /** @var QuestionHelper $helper */
    $helper = $this->getHelper('question');

    $email = $input->getOption('email');
    if ($email === null) {
      $emailQuestion = new Question('Enter email: ');
      $email = $helper->ask($input, $output, $emailQuestion);
    }

    $password = $input->getOption('password');
    if ($password === null) {
      $passwordQuestion = new Question('Enter new password: ');
      $passwordQuestion->setHidden(true);
      $passwordQuestion->setHiddenFallback(false);
      $password = $helper->ask($input, $output, $passwordQuestion);

      $confirmQuestion = new Question('Confirm password: ');
      $confirmQuestion->setHidden(true);
      $confirmQuestion->setHiddenFallback(false);
      $confirm = $helper->ask($input, $output, $confirmQuestion);

      if ($password !== $confirm) {
        $output->writeln('<error>Error: Passwords do not match.</error>');
        return Command::FAILURE;
      }
    }

    try {
      $command = new ResetPassword(
        email: $email,
        password: $password
      );

      $this->handleSync($command);

      $output->writeln(sprintf(
        '<info>Password reset for %s ✓</info>',
        $email
      ));

      return Command::SUCCESS;
    } catch (\Exception $e) {
      $output->writeln(sprintf('<error>Error: %s</error>', $e->getMessage()));
      return Command::FAILURE;
    }
  }
}
