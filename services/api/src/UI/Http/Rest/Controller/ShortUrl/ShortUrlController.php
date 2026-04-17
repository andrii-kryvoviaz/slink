<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\ShortUrl;

use Slink\Share\Application\Query\FindShortUrlByCode\FindShortUrlByCodeQuery;
use Slink\Share\Application\Service\ShareAccessGuard;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/i/{code}', name: 'short_url_redirect', methods: ['GET'])]
#[Route(path: '/c/{code}', name: 'short_url_collection_redirect', methods: ['GET'])]
final readonly class ShortUrlController {
  use QueryTrait;

  public function __construct(
    #[Autowire('%env(ORIGIN)%')]
    private string $origin,
    private ShareAccessGuard $accessGuard,
  ) {
  }

  public function __invoke(string $code): RedirectResponse {
    $shortUrl = $this->ask(new FindShortUrlByCodeQuery($code));

    if ($shortUrl === null) {
      throw new NotFoundException();
    }

    $share = $shortUrl->getShare();

    if (!$this->accessGuard->allows($share)) {
      throw new NotFoundException();
    }

    $targetPath = $share->getTargetPath();

    if (str_starts_with($targetPath, '/')) {
      $targetPath = $this->origin . $targetPath;
    }

    return ApiResponse::redirect($targetPath);
  }
}
