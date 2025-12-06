<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\ShortUrl;

use Slink\Share\Application\Query\FindShortUrlByCode\FindShortUrlByCodeQuery;
use Slink\Share\Infrastructure\ReadModel\View\ShortUrlView;
use Slink\Shared\Application\Query\QueryTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/i/{code}', name: 'short_url_redirect', methods: ['GET'])]
final readonly class ShortUrlController {
  use QueryTrait;

  public function __construct(
    #[Autowire('%env(ORIGIN)%')]
    private string $origin,
  ) {
  }

  public function __invoke(string $code): Response {
    $shortUrl = $this->ask(new FindShortUrlByCodeQuery($code));

    if ($shortUrl === null) {
      return new Response('Not Found', Response::HTTP_NOT_FOUND);
    }

    $targetUrl = $shortUrl->getTargetUrl();

    if (str_starts_with($targetUrl, '/')) {
      $targetUrl = $this->origin . $targetUrl;
    }

    return new RedirectResponse($targetUrl, Response::HTTP_FOUND);
  }
}
