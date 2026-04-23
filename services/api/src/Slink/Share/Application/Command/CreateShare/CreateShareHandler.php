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
    $shareable = $params->getShareable();
    $context = $this->shareService->buildContext($shareable);

    $share = $this->shareStore->findByTargetPath($params->getTargetPath());

    if ($share === null) {
      $share = Share::create(
        ID::generate(),
        $shareable,
        $params->getTargetPath(),
        DateTime::now(),
        $context,
      );

      $this->shareStore->store($share);
      return CreateShareResult::created($share);
    }

    $share->addShortUrl($context->getShortUrlId(), $context->getShortCode());

    if ($shareable->getShareableType()->autoPublishOnCreate()) {
      $share->publish();
    }

    $this->shareStore->store($share);
    return CreateShareResult::existing($share);
  }
}
