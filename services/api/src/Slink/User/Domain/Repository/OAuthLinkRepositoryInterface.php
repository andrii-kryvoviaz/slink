<?php

declare(strict_types=1);

namespace Slink\User\Domain\Repository;

use Slink\User\Domain\ValueObject\OAuth\OAuthSubject;
use Slink\User\Infrastructure\ReadModel\View\OAuthLinkView;

interface OAuthLinkRepositoryInterface {
  public function findById(string $id): ?OAuthLinkView;

  public function findBySubject(OAuthSubject $subject): ?OAuthLinkView;

  /**
   * @return array<int, OAuthLinkView>
   */
  public function findByUserId(string $userId): array;

  public function save(OAuthLinkView $link): void;

  public function delete(OAuthLinkView $link): void;
}
