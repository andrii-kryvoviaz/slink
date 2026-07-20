<?php

declare(strict_types=1);

namespace UI\Console\Command\User;

use Ramsey\Uuid\Uuid;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\User\Application\Command\PurgeUser\PurgeUserCommand as PurgeUser;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Exception\InvalidEmailException;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Infrastructure\ReadModel\View\UserView;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

#[AsCommand(
  name: 'user:purge',
  description: 'Permanently purge a user account and all owned content'
)]
final class PurgeUserCommand extends Command {
  use CommandTrait;

  public function __construct(
    private readonly UserRepositoryInterface $userRepository,
  ) {
    parent::__construct();
  }

  public function configure(): void {
    $this
      ->addArgument('user', InputArgument::OPTIONAL, 'User email or UUID')
      ->addOption('force', 'f', InputOption::VALUE_NONE, 'Purge even if the user is not soft-deleted')
      ->addOption('all-deleted', null, InputOption::VALUE_NONE, 'Purge every soft-deleted user');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    if ($input->getOption('all-deleted')) {
      return $this->purgeAllDeleted($input, $output);
    }

    $identifier = $input->getArgument('user');

    if (!$identifier) {
      $output->writeln('<error>Provide a user email or UUID, or use --all-deleted</error>');
      return Command::FAILURE;
    }

    try {
      $user = $this->resolveUser($identifier);
    } catch (NotFoundException | InvalidEmailException) {
      $output->writeln(sprintf('<error>User `%s` was not found</error>', $identifier));
      return Command::FAILURE;
    }

    if ($user->getStatus() !== UserStatus::Deleted->value && !$input->getOption('force')) {
      $output->writeln(sprintf(
        '<error>User `%s` is not soft-deleted. Use --force to purge anyway.</error>',
        $user->getEmail()
      ));
      return Command::FAILURE;
    }

    if (!$input->isInteractive() && !$input->getOption('force')) {
      $output->writeln('<error>Non-interactive purge requires --force</error>');
      return Command::FAILURE;
    }

    $question = sprintf(
      'Purge user `%s` (%s)? This permanently removes the account and all owned content.',
      $user->getEmail(),
      $user->getUuid()
    );

    if (!$this->confirm($input, $output, $question)) {
      return Command::SUCCESS;
    }

    $this->handle(new PurgeUser($user->getUuid()));

    $output->writeln(sprintf('<info>User `%s` has been purged ✓</info>', $user->getEmail()));

    return Command::SUCCESS;
  }

  private function purgeAllDeleted(InputInterface $input, OutputInterface $output): int {
    $users = $this->userRepository->findByStatus(UserStatus::Deleted);

    if (!$users) {
      $output->writeln('<comment>No soft-deleted users found.</comment>');
      return Command::SUCCESS;
    }

    $question = sprintf(
      'Purge %d soft-deleted user(s)? This permanently removes the accounts and all owned content.',
      count($users)
    );

    if (!$this->confirm($input, $output, $question)) {
      return Command::SUCCESS;
    }

    foreach ($users as $user) {
      $this->handle(new PurgeUser($user->getUuid()));
      $output->writeln(sprintf('<info>User `%s` has been purged ✓</info>', $user->getEmail()));
    }

    return Command::SUCCESS;
  }

  private function resolveUser(string $identifier): UserView {
    if (Uuid::isValid($identifier)) {
      return $this->userRepository->one(ID::fromString($identifier));
    }

    return $this->userRepository->oneByEmail(Email::fromString($identifier));
  }

  private function confirm(InputInterface $input, OutputInterface $output, string $message): bool {
    if (!$input->isInteractive()) {
      return true;
    }

    /** @var QuestionHelper $helper */
    $helper = $this->getHelper('question');

    return (bool) $helper->ask($input, $output, new ConfirmationQuestion($message . ' [y/N] ', false));
  }
}
