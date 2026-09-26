<?php

declare(strict_types=1);

namespace UI\Console\Command\Demo;

use Slink\User\Infrastructure\Auth\JwtUser;
use Slink\User\Infrastructure\ReadModel\View\UserView;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

final class DemoSession {
  public function __construct(
    private readonly TokenStorageInterface $tokenStorage,
  ) {
  }

  /**
   * @template T
   * @param \Closure(): T $action
   * @return T
   */
  public function actingAs(UserView $user, \Closure $action): mixed {
    /** @var non-empty-string $uuid */
    $uuid = $user->getUuid();
    $jwtUser = JwtUser::createFromPayload($uuid, ['roles' => $user->getRoles()]);
    $previous = $this->tokenStorage->getToken();
    $this->tokenStorage->setToken(new UsernamePasswordToken($jwtUser, 'api', $jwtUser->getRoles()));

    try {
      return $action();
    } finally {
      $this->tokenStorage->setToken($previous);
    }
  }

  /**
   * @template T
   * @param \Closure(): T $action
   * @return T
   */
  public function at(\DateTimeImmutable $moment, \Closure $action): mixed {
    $clock = Clock::get();
    Clock::set(new MockClock($moment));

    try {
      return $action();
    } finally {
      Clock::set($clock);
    }
  }
}
