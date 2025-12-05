<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\ShortUrl;

use Slink\Share\Domain\Repository\ShortUrlRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/i/{code}', name: 'short_url_redirect', methods: ['GET'])]
final readonly class ShortUrlController {
  public function __construct(
    private ShortUrlRepositoryInterface $shortUrlRepository,
  ) {
  }

  public function __invoke(string $code): Response {
    $shortUrl = $this->shortUrlRepository->findByShortCode($code);

    if ($shortUrl === null) {
      return new Response('Not Found', Response::HTTP_NOT_FOUND);
    }

    return new RedirectResponse($shortUrl->getTargetUrl(), Response::HTTP_FOUND);
  }
}
