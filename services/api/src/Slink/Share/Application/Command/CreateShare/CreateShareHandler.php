<?php

declare(strict_types=1);

namespace Slink\Share\Application\Command\CreateShare;

use Slink\Share\Domain\Repository\ShareStoreRepositoryInterface;
use Slink\Share\Domain\Service\ShareServiceInterface;
use Slink\Share\Domain\Share;
use Slink\Share\Domain\ValueObject\ShareParams;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class CreateShareHandler implements CommandHandlerInterface {
  public function __construct(
    private ShareStoreRepositoryInterface $shareStore,
    private ShareServiceInterface $shareService,
  ) {
  }

  public function __invoke(CreateShareCommand $command): CreateShareResult {
    $params = $command->getParams();

    $existingShare = $this->shareStore->findByTargetUrl($params->getTargetPath());
    if ($existingShare !== null) {
      return CreateShareResult::existing($existingShare);
    }

    $share = $this->createShare($params);
    return CreateShareResult::created($share);
  }

  private function createShare(ShareParams $params): Share {
    $shareable = $params->getShareable();
    $context = $this->shareService->buildContext($shareable);

    $share = Share::create(
      ID::generate(),
      $shareable,
      $params->getTargetPath(),
      DateTime::now(),
      $context,
    );

    $this->shareStore->store($share);

    return $share;
  }
}
