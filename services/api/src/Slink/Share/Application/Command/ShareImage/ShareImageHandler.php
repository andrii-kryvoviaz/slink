<?php

declare(strict_types=1);

namespace Slink\Share\Application\Command\ShareImage;

use Slink\Share\Domain\Repository\ShareStoreRepositoryInterface;
use Slink\Share\Domain\Service\ShortCodeGeneratorInterface;
use Slink\Share\Domain\Share;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ShareImageHandler implements CommandHandlerInterface {
  public function __construct(
    private ShareStoreRepositoryInterface $shareStore,
    private ShortCodeGeneratorInterface $shortCodeGenerator,
  ) {
  }

  public function __invoke(ShareImageCommand $command): Share {
    $share = Share::create(
      ID::generate(),
      ID::fromString($command->getImageId()),
      $command->getTargetUrl(),
      DateTime::now(),
    );

    if ($command->shouldCreateShortUrl()) {
      $shortCode = $this->shortCodeGenerator->generate();
      $share->createShortUrl(ID::generate(), $shortCode);
    }

    $this->shareStore->store($share);

    return $share;
  }
}
