<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\Repository;

use EventSauce\EventSourcing\MessageDispatcher;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Domain\Repository\ShareStoreRepositoryInterface;
use Slink\Share\Domain\Share;
use Slink\Share\Domain\ValueObject\TargetPath;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractViewBackedStore;

/**
 * @extends AbstractViewBackedStore<Share, ShareView>
 */
final class ShareStore extends AbstractViewBackedStore implements ShareStoreRepositoryInterface {
  public function __construct(
    MessageDispatcher $messageDispatcher,
    private readonly ShareRepositoryInterface $shareRepository,
  ) {
    parent::__construct($messageDispatcher);
  }

  protected static function getAggregateRootClass(): string {
    return Share::class;
  }

  protected function findRootView(ID $id): object {
    $view = $this->shareRepository->findById($id->toString());

    if ($view === null) {
      throw new NotFoundException();
    }

    return $view;
  }

  protected function hydrate(object $view): object {
    return Share::fromView($view, $view->getShortUrl());
  }

  public function get(ID $id): Share {
    return $this->retrieve($id);
  }

  public function findByTargetPath(TargetPath $targetPath): ?Share {
    $shareView = $this->shareRepository->findByTargetPath($targetPath);
    if ($shareView === null) {
      return null;
    }
    return $this->get(ID::fromString($shareView->getId()));
  }

  public function store(Share $share): void {
    $this->persist($share);
  }
}
