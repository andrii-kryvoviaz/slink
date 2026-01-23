<?php

declare(strict_types=1);

namespace Slink\Share\Application\Service;

use Slink\Share\Domain\Service\ShareFeatureHandlerInterface;
use Slink\Share\Domain\Service\ShareServiceInterface;
use Slink\Share\Domain\Share;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Domain\ValueObject\ShareContext;
use Slink\Share\Domain\ValueObject\ShareResult;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

#[AsAlias(ShareServiceInterface::class)]
final readonly class ShareService implements ShareServiceInterface {
  /**
   * @param iterable<ShareFeatureHandlerInterface> $featureHandlers
   */
  public function __construct(
    #[Autowire('%env(ORIGIN)%')]
    private string $origin,
    #[AutowireIterator('share.feature_handler')]
    private iterable $featureHandlers,
  ) {}

  public function buildContext(ShareableReference $shareable): ShareContext {
    $context = ShareContext::for($shareable);

    foreach ($this->featureHandlers as $handler) {
      if ($handler->supports($context)) {
        $context = $handler->enhance($context);
      }
    }

    return $context;
  }

  public function resolveUrl(ShareView|Share $share): string {
    $shortCode = $share instanceof ShareView
      ? $share->getShortUrl()?->getShortCode()
      : $share->getShortCode();
    $type = $share->getShareable()->getShareableType();
    $prefix = $type->urlPrefix();

    if ($shortCode === null) {
      return rtrim($this->origin, '/') . $share->getTargetUrl();
    }

    return rtrim($this->origin, '/') . '/' . $prefix . '/' . $shortCode;
  }

  public function share(string $shareableId, string $targetUrl): ShareResult {
    return ShareResult::signed($targetUrl);
  }
}
