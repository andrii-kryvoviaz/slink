<?php

declare(strict_types=1);

namespace Slink\Share\Application\Service;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Share\Application\Command\ShareImage\ShareImageCommand;
use Slink\Share\Application\Query\FindShareByTargetUrl\FindShareByTargetUrlQuery;
use Slink\Share\Domain\Service\ShareServiceInterface;
use Slink\Share\Domain\Share;
use Slink\Share\Domain\ValueObject\ShareResult;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Application\Query\QueryBusInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(ShareServiceInterface::class)]
final readonly class ShareService implements ShareServiceInterface {
  public function __construct(
    private CommandBusInterface $commandBus,
    private QueryBusInterface $queryBus,
    private ConfigurationProviderInterface $configurationProvider,
  ) {}

  public function isShorteningEnabled(): bool {
    return $this->configurationProvider->get('share.enableUrlShortening') ?? true;
  }

  public function share(string $imageId, string $targetUrl): ShareResult {
    if (!$this->isShorteningEnabled()) {
      return ShareResult::signed($targetUrl);
    }

    /** @var ShareView|null $existingShare */
    $existingShare = $this->queryBus->ask(new FindShareByTargetUrlQuery($targetUrl));

    if ($existingShare?->getShortUrl() !== null) {
      return ShareResult::shortUrl($existingShare->getShortUrl()->getShortCode());
    }

    /** @var Share $share */
    $share = $this->commandBus->handleSync(
      new ShareImageCommand($imageId, $targetUrl, true)
    );

    return ShareResult::shortUrl($share->getShortCode());
  }
}
