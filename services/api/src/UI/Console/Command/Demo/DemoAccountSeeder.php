<?php

declare(strict_types=1);

namespace UI\Console\Command\Demo;

use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\User\Application\Command\CreateUser\CreateUserCommand;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Slink\User\Domain\ValueObject\Username;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class DemoAccountSeeder {
  use CommandTrait;

  private const array ACCOUNTS = [
    'alice' => ['Alice', 'alice@example.com'],
    'bob' => ['Bob', 'bob@example.com'],
    'carol' => ['Carol', 'carol@example.com'],
  ];

  public function __construct(
    private readonly UserRepositoryInterface $userRepository,
    private readonly DemoImageSeeder $imageSeeder,
    private readonly DemoSession $session,
  ) {
  }

  /**
   * @return array<string, UserView>
   */
  public function seed(DemoSeedReport $report): array {
    $accounts = [];

    foreach (self::ACCOUNTS as $username => [$displayName, $email]) {
      $account = $report->attempt($username, fn() => $this->ensureAccount($username, $displayName, $email, $report));

      if ($account !== null) {
        $accounts[$username] = $account;
      }
    }

    return $accounts;
  }

  /**
   * @param array<string, UserView> $accounts
   * @return array<string, ImageView>
   */
  public function seedPictures(array $accounts, string $picturesPath, DemoSeedReport $report): array {
    $images = [];

    foreach ($accounts as $username => $account) {
      $images += $this->seedFolder($account, $username, $picturesPath . '/' . $username, $report);
    }

    return $images;
  }

  public function find(string $username): ?UserView {
    try {
      return $this->userRepository->oneByUsername(Username::fromString($username));
    } catch (NotFoundException) {
      return null;
    }
  }

  private function ensureAccount(string $username, string $displayName, string $email, DemoSeedReport $report): UserView {
    $existing = $this->find($username);

    if ($existing !== null) {
      $report->skipped($username);

      return $existing;
    }

    $this->handle(new CreateUserCommand($email, bin2hex(random_bytes(32)), $username, $displayName, UserStatus::Active));
    $report->created($username);

    return $this->userRepository->oneByUsername(Username::fromString($username));
  }

  /**
   * @return array<string, ImageView>
   */
  private function seedFolder(UserView $account, string $username, string $folder, DemoSeedReport $report): array {
    if (!is_dir($folder)) {
      $report->skipped($username . '/');

      return [];
    }

    $userId = ID::fromString($account->getUuid());
    $images = $this->session->actingAs($account, fn() => $this->imageSeeder->seed($userId, $folder, $report));
    $keys = array_map(static fn(string $fileName): string => $username . '/' . $fileName, array_keys($images));

    return array_combine($keys, $images);
  }
}
