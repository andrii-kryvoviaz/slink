<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Settings;

use Slink\Settings\Application\Query\GetBrandingLogo\GetBrandingLogoQuery;
use Slink\Settings\Domain\Enum\BrandingLogoFormat;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use UI\Http\Rest\Response\ContentResponse;

#[AsController]
#[Route(path: '/branding/logo', name: 'get_branding_logo', methods: ['GET'])]
final readonly class GetBrandingLogoController {
  use QueryTrait;

  public function __invoke(Request $request): ContentResponse {
    /** @var Item $item */
    $item = $this->ask(new GetBrandingLogoQuery(versioned: $request->query->has('v')));

    $response = ContentResponse::file($item);

    if (BrandingLogoFormat::isSvgMimeType($item->type)) {
      $response->headers->set('Content-Security-Policy', "default-src 'none'; style-src 'unsafe-inline'; sandbox");
    }

    return $response;
  }
}
