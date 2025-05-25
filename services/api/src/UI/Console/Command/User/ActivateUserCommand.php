<?php

declare(strict_types=1);

namespace UI\Console\Command\User;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Application\Command\ChangeUserStatus\ChangeUserStatusCommand;
use Slink\User\Domain\Enum\UserStatus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'user:activate',
    description: 'Activate user account'
)]
final class ActivateUserCommand extends AbstractUserCommand {
  use CommandTrait;
  
  /**
   * @param InputInterface $input
   * @param OutputInterface $output
   * @return int
   */
  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $userItem = $this->retrieveUser($input);
    
    /** @var array<string, mixed> $user */
    $user = $userItem->resource;
    [$id, $email, $display_name] = array_values($user);
    
    $this->handle(new ChangeUserStatusCommand($id, (UserStatus::Active)->value));
    
    $output->writeln(sprintf('User `%s` has been activated ✓', $email));
    
    return Command::SUCCESS;
  }
}