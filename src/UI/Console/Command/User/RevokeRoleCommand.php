<?php

declare(strict_types=1);

namespace UI\Console\Command\User;

use Slink\Shared\Application\Command\CommandTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Slink\User\Application\Command\RevokeRole\RevokeRoleCommand as RevokeRole;

#[AsCommand(
  name: 'user:revoke:role',
  description: 'Revoke role from user'
)]
final class RevokeRoleCommand extends AbstractUserCommand {
  use CommandTrait;
  
  /**
   * @return void
   */
  public function configure(): void {
    parent::configure();
    
    $this->addArgument('role', InputOption::VALUE_REQUIRED, 'Role to revoke');
  }
  
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
    [$id, $email] = array_values($user);
    $role = $input->getArgument('role');
    
    $this->handle(new RevokeRole($id, $role));
    
    $output->writeln(sprintf('User `%s` has been revoked role `%s`', $email, $role));
    
    return Command::SUCCESS;
  }
  
}