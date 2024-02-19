<?php

declare(strict_types=1);

namespace UI\Console\Command\User;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Application\Command\ChangeUserStatus\ChangeUserStatusCommand;
use Slink\User\Application\Query\User\FindByEmail\FindByEmailQuery;
use Slink\User\Application\Query\User\FindUserById\FindUserByIdQuery;
use Slink\User\Domain\Enum\UserStatus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'user:activate',
    description: 'Activate user account'
)]
final class ActivateUserCommand extends Command {
  use CommandTrait;
  use QueryTrait;
  
  /**
   * @return void
   */
  public function configure(): void {
    $this->addOption('email', null, InputOption::VALUE_OPTIONAL, 'User email');
    $this->addOption('uuid', null, InputOption::VALUE_OPTIONAL, 'User UUID');
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
    [$id, $email, $display_name] = array_values($user);
    
    $this->handle(new ChangeUserStatusCommand($id, (UserStatus::Active)->value));
    
    $output->writeln(sprintf('User `%s` has been activated ✓', $email));
    
    return Command::SUCCESS;
  }
  
  /**
   * @param InputInterface $input
   * @return Item
   */
  private function retrieveUser(InputInterface $input): Item {
    if($email = $input->getOption('email')) {
       return $this->ask(new FindByEmailQuery($email));
    }
    
    $uuid = $input->getOption('uuid');
    return $this->ask(new FindUserByIdQuery($uuid));
  }
  
  /**
   * @param InputInterface $input
   * @param OutputInterface $output
   * @return void
   */
  #[\Override]
  protected function interact(InputInterface $input, OutputInterface $output): void {
    if($input->getOption('email') && $input->getOption('uuid')) {
      throw new \InvalidArgumentException('You can only use one option: email or uuid');
    }
    
    if(!$input->getOption('email') && !$input->getOption('uuid')) {
      throw new \InvalidArgumentException('You must use one option: email or uuid');
    }
  }
}